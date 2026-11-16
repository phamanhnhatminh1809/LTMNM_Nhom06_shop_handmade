<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Tạo file Excel (.xlsx) chuẩn Office Open XML bằng ZipArchive thuần PHP.
 */
class XlsxExportService
{
    public const STYLE_DEFAULT = 0;
    public const STYLE_TITLE = 1;
    public const STYLE_META = 2;
    public const STYLE_SECTION = 3;
    public const STYLE_HEADER = 4;
    public const STYLE_TEXT = 5;
    public const STYLE_TEXT_CENTER = 6;
    public const STYLE_INT = 7;
    public const STYLE_CURRENCY = 8;
    public const STYLE_PERCENT = 9;
    public const STYLE_TOTAL_TEXT = 10;
    public const STYLE_TOTAL_INT = 11;
    public const STYLE_TOTAL_CURRENCY = 12;
    public const STYLE_TOTAL_PERCENT = 13;

    /** @var array<string, int> */
    private array $sharedStringMap = [];

    /** @var array<int, string> */
    private array $sharedStrings = [];

    /**
     * Tạo file .xlsx tạm thời trên đĩa và trả về đường dẫn tuyệt đối.
     *
     * @param  array<int, array{
     *     name: string,
     *     rows: array<int, array<int, array{value: mixed, type?: string, style?: int}>>,
     *     merges?: array<int, string>,
     *     row_heights?: array<int, float|int>,
     *     min_widths?: array<int, float|int>
     * }>  $sheets
     */
    public function buildToTempFile(array $sheets): string
    {
        if (empty($sheets)) {
            throw new RuntimeException('Danh sách sheet không được để trống.');
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Máy chủ chưa bật PHP ZipArchive extension để xuất file .xlsx.');
        }

        $this->sharedStringMap = [];
        $this->sharedStrings = [];

        $tempBase = tempnam(sys_get_temp_dir(), 'xlsx_');
        if ($tempBase === false) {
            throw new RuntimeException('Không thể tạo tệp tạm để xuất Excel.');
        }

        $xlsxPath = $tempBase . '.xlsx';
        @unlink($tempBase);

        $zip = new ZipArchive();
        if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Không thể khởi tạo tệp nén .xlsx.');
        }

        $normalizedSheets = $this->normalizeSheets($sheets);

        foreach ($normalizedSheets as $index => $sheet) {
            $sheetNumber = $index + 1;
            $sheetXml = $this->buildWorksheetXml($sheet);
            $zip->addFromString("xl/worksheets/sheet{$sheetNumber}.xml", $sheetXml);
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml(count($normalizedSheets)));
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($normalizedSheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml(count($normalizedSheets)));
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());
        $zip->addFromString('xl/sharedStrings.xml', $this->buildSharedStringsXml());

        $zip->close();

        return $xlsxPath;
    }

    /**
     * Chuẩn hóa tên sheet hợp lệ cho Excel (tối đa 31 ký tự, duy nhất).
     *
     * @param  array<int, array<string, mixed>>  $sheets
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSheets(array $sheets): array
    {
        $usedNames = [];
        $result = [];

        foreach ($sheets as $idx => $sheet) {
            $rawName = (string) ($sheet['name'] ?? ('Sheet ' . ($idx + 1)));
            $clean = preg_replace('/[\\\\\/\?\*\[\]:]/u', ' ', $rawName) ?? $rawName;
            $clean = trim(preg_replace('/\s+/u', ' ', $clean) ?? $clean);
            if ($clean === '') {
                $clean = 'Sheet ' . ($idx + 1);
            }
            $clean = mb_substr($clean, 0, 31, 'UTF-8');

            $candidate = $clean;
            $suffix = 2;
            while (in_array(mb_strtolower($candidate, 'UTF-8'), $usedNames, true)) {
                $suffixStr = " ({$suffix})";
                $candidate = mb_substr($clean, 0, max(1, 31 - mb_strlen($suffixStr, 'UTF-8')), 'UTF-8') . $suffixStr;
                $suffix++;
            }

            $usedNames[] = mb_strtolower($candidate, 'UTF-8');
            $sheet['name'] = $candidate;
            $result[] = $sheet;
        }

        return $result;
    }

    /**
     * Đăng ký chuỗi vào bảng sharedStrings và trả về index.
     */
    private function internString(string $text): int
    {
        $sanitized = $this->sanitizeXmlText($text);
        if (array_key_exists($sanitized, $this->sharedStringMap)) {
            return $this->sharedStringMap[$sanitized];
        }

        $idx = count($this->sharedStrings);
        $this->sharedStrings[] = $sanitized;
        $this->sharedStringMap[$sanitized] = $idx;

        return $idx;
    }

    /**
     * Loại bỏ ký tự điều khiển không hợp lệ trong XML 1.0.
     */
    private function sanitizeXmlText(string $value): string
    {
        return (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($this->sanitizeXmlText($value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Đổi chỉ số cột (1-based) sang tên cột Excel (A, B, ..., Z, AA, ...).
     */
    public static function columnLetter(int $colIndex): string
    {
        $letter = '';
        $n = max(1, $colIndex);
        while ($n > 0) {
            $rem = ($n - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $n = intdiv($n - 1, 26);
        }

        return $letter;
    }

    /**
     * @param  array<string, mixed>  $sheet
     */
    private function buildWorksheetXml(array $sheet): string
    {
        $rows = (array) ($sheet['rows'] ?? []);
        $merges = (array) ($sheet['merges'] ?? []);
        $rowHeights = (array) ($sheet['row_heights'] ?? []);
        $minWidths = (array) ($sheet['min_widths'] ?? []);

        $mergedRanges = [];
        $horizontalMergeAnchors = [];
        foreach ($merges as $range) {
            if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', strtoupper(trim((string) $range)), $m)) {
                $mergedRanges[] = $m[0];
                if ($m[1] !== $m[3]) {
                    $horizontalMergeAnchors[$m[1] . $m[2]] = true;
                }
            }
        }

        $colWidths = [];
        foreach ($minWidths as $colNumber => $minWidth) {
            $colWidths[(int) $colNumber] = (float) $minWidth;
        }

        $rowsXml = '';
        foreach ($rows as $rowIdxZero => $cells) {
            $rowNumber = $rowIdxZero + 1;
            $heightAttr = isset($rowHeights[$rowNumber])
                ? ' ht="' . (float) $rowHeights[$rowNumber] . '" customHeight="1"'
                : '';

            $cellsXml = '';

            foreach ((array) $cells as $colIdxZero => $cell) {
                $colNumber = $colIdxZero + 1;
                $colRef = self::columnLetter($colNumber) . $rowNumber;
                $value = $cell['value'] ?? '';
                $style = (int) ($cell['style'] ?? self::STYLE_DEFAULT);
                $type = $cell['type'] ?? (is_int($value) || is_float($value) ? 'n' : 's');

                if (!isset($horizontalMergeAnchors[$colRef])) {
                    $displayLen = $type === 'n'
                        ? mb_strlen(number_format((float) $value, 0, ',', '.'), 'UTF-8') + 4
                        : mb_strlen((string) $value, 'UTF-8') + 3;
                    $colWidths[$colNumber] = max($colWidths[$colNumber] ?? 10.0, min(52.0, (float) $displayLen));
                } else {
                    $colWidths[$colNumber] = max($colWidths[$colNumber] ?? 14.0, 14.0);
                }

                if ($type === 'n' && is_numeric($value)) {
                    $numStr = is_int($value) ? (string) $value : rtrim(rtrim(sprintf('%.6F', (float) $value), '0'), '.');
                    $cellsXml .= '<c r="' . $colRef . '" s="' . $style . '" t="n"><v>' . $numStr . '</v></c>';
                } else {
                    $strIndex = $this->internString((string) $value);
                    $cellsXml .= '<c r="' . $colRef . '" s="' . $style . '" t="s"><v>' . $strIndex . '</v></c>';
                }
            }

            $rowsXml .= '<row r="' . $rowNumber . '"' . $heightAttr . '>' . $cellsXml . '</row>';
        }

        $colsXml = '';
        if (!empty($colWidths)) {
            ksort($colWidths);
            $colsXml = '<cols>';
            foreach ($colWidths as $colNum => $width) {
                $w = round(max(10.0, $width), 2);
                $colsXml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $w . '" customWidth="1"/>';
            }
            $colsXml .= '</cols>';
        }

        $mergeCellsXml = '';
        if (!empty($mergedRanges)) {
            $mergeCellsXml = '<mergeCells count="' . count($mergedRanges) . '">';
            foreach ($mergedRanges as $range) {
                $mergeCellsXml .= '<mergeCell ref="' . $this->escapeXml($range) . '"/>';
            }
            $mergeCellsXml .= '</mergeCells>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $colsXml
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeCellsXml
            . '</worksheet>';
    }

    private function buildContentTypesXml(int $sheetCount): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . $overrides
            . '</Types>';
    }

    private function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    /**
     * @param  array<int, array<string, mixed>>  $sheets
     */
    private function buildWorkbookXml(array $sheets): string
    {
        $sheetsXml = '';
        foreach ($sheets as $index => $sheet) {
            $sheetId = $index + 1;
            $sheetsXml .= '<sheet name="' . $this->escapeXml((string) $sheet['name']) . '" sheetId="' . $sheetId . '" r:id="rId' . $sheetId . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRelsXml(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }

        $stylesId = $sheetCount + 1;
        $stringsId = $sheetCount + 2;

        $rels .= '<Relationship Id="rId' . $stylesId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $rels .= '<Relationship Id="rId' . $stringsId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="3">'
            . '<numFmt numFmtId="164" formatCode="#,##0&quot; đ&quot;"/>'
            . '<numFmt numFmtId="165" formatCode="#,##0"/>'
            . '<numFmt numFmtId="166" formatCode="0.0%"/>'
            . '</numFmts>'
            . '<fonts count="5">'
            . '<font><sz val="11"/><color rgb="FF251914"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FF792A16"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FF792A16"/><name val="Calibri"/></font>'
            . '<font><i/><sz val="10"/><color rgb="FF6B5B55"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF792A16"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF9ECE7"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF8F6"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="3">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFE0D3CE"/></left><right style="thin"><color rgb="FFE0D3CE"/></right><top style="thin"><color rgb="FFE0D3CE"/></top><bottom style="thin"><color rgb="FFE0D3CE"/></bottom><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFE0D3CE"/></left><right style="thin"><color rgb="FFE0D3CE"/></right><top style="thin"><color rgb="FF792A16"/></top><bottom style="double"><color rgb="FF792A16"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="14">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="166" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="165" fontId="3" fillId="3" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="3" fillId="3" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="166" fontId="3" fillId="3" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function buildSharedStringsXml(): string
    {
        $total = count($this->sharedStrings);
        $items = '';
        foreach ($this->sharedStrings as $str) {
            $items .= '<si><t xml:space="preserve">' . $this->escapeXml($str) . '</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $total . '" uniqueCount="' . $total . '">'
            . $items
            . '</sst>';
    }
}
