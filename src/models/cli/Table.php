<?php

namespace mmvc\models\cli;

class Table extends \mmvc\models\BaseModel
{
    const ALIGN_LEFT = 0;
    const ALIGN_RIGHT = 1;
    const ALIGN_CENTER = 2;

    const BORDER_TYPE_BOLD = 1;
    const BORDER_TYPE_NORMAL = 0;

    const FLAG_FILTER_TERMINAL_COLORS = 1;
    const FLAG_EMPTY = 0;

    const REGEXP_COLOR_CODE_BEGIN = '/\\033\\[[0-9]\\;[0-9]{2}m/';
    const REGEXP_COLOR_CODE_END = '/\\033\\[0m/';

    /**
     * @var array
     */
    protected $header = [];

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var integer
     */
    protected $rowsCount = 0;

    protected $lengthPerColumn = [];

    protected $flags = 0;

    protected $cachedRowBorder = '';

    protected $cachedTableBorder = '';

    protected $ignoreColorCodes = true;

    public function __construct(int $flags = 0)
    {
        $this->flags = $flags;
        parent::__construct();
    }

    public function setIgnoreColorCodes(bool $value)
    {
        $this->ignoreColorCodes = $value;
    }

    public function getColumnCount(): int
    {
        return count($this->header);
    }

    public function setHeader(array $header)
    {
        if ($this->getColumnCount() > 0)
        {
            throw new \Exception('Header already set');
        }

        $this->header = $header;
        $i = 0;
        foreach ($header as $columnName)
        {
            $this->lengthPerColumn[$i] = mb_strlen((string)$columnName) + 2;
            $i++;
        }
    }

    /**
     * Возвращает количество символов, которые нужно игнорировать при расчете длины строки, т.к. это
     * спец. символы для форматирования текста в консоли
     * @param string $pattern
     * @param string $string
     * @return int
     */
    protected function considerColorCodes(string $pattern, string $string): int
    {
        $matches = [];

        $count = preg_match($pattern, $string, $matches);
        if ($count === false || $count === 0)
        {
            return 0;
        }

        $count = 0;
        foreach ($matches as $match)
        {
            $count += mb_strlen($match);
        }

        return $count;
    }

    /**
     * Расчет максимальной длины каждого значения в таблице без учета длины заголовков
     * (длина заголовков проверяется в вызове setHeader
     */
    protected function calcMaxLenPerRow()
    {
        foreach ($this->rows as $row)
        {
            $i = 0;
            foreach ($row as $column)
            {
                $columnLen = mb_strlen((string)$column);
                if (!$this->ignoreColorCodes)
                {
                    $columnLen -= $this->considerColorCodes(Table::REGEXP_COLOR_CODE_BEGIN, $column);
                    $columnLen -= $this->considerColorCodes(Table::REGEXP_COLOR_CODE_END, $column);
                }

                if ($columnLen > $this->lengthPerColumn[$i])
                {
                    $this->lengthPerColumn[$i] = $columnLen;
                    //  $this->lengthPerColumn[$i] += ceil($columnLen * 0.30);
                }
                $i++;
            }
        }
    }

    /**
     * @param int $columnNumber
     * @return string
     * @throws Exception
     */
    protected function generateEmptyCell(int $columnNumber, int $stringLen) : string
    {
        if(!array_key_exists($columnNumber, $this->lengthPerColumn))
        {
            throw new \Exception('Undefined column with index '.$columnNumber);
        }

        $len = $this->lengthPerColumn[$columnNumber] - $stringLen;

        return str_repeat(' ', $len);
    }

    /**
     * Форматирование ячейки (мостим значение строки в пробелы)
     * @param string $string исходное значение
     * @param int $align выравнивание
     * @param int $columnNumber
     * @return string
     */
    protected function formatCell(string $string, int $align, int $columnNumber) : string
    {
        $len = mb_strlen($string);
        if (!$this->ignoreColorCodes)
        {
            $len -= $this->considerColorCodes(Table::REGEXP_COLOR_CODE_BEGIN, $string);
            $len -= $this->considerColorCodes(Table::REGEXP_COLOR_CODE_END, $string);
        }
        $spaces = $this->generateEmptyCell($columnNumber, $len);
        switch ($align)
        {
            case Table::ALIGN_LEFT:
                return "{$string}{$spaces}";
            case Table::ALIGN_RIGHT:
                return "{$spaces}{$string}";
            case Table::ALIGN_CENTER:
                throw new \Exception('Not implemented yet');
            default:
                throw new \Exception("Unknown align flag({$align})");
        }
        return $spaces;
    }

    public function isLastColumn(int $index): bool
    {
        return $index === $this->getColumnCount();
    }

    protected function outBorder(int $borderType, bool $useColumnDelemiter = false): string
    {
        $borderCharacter = '=';
        if ($borderType === Table::BORDER_TYPE_NORMAL)
        {
            $borderCharacter = '-';
        }

        if ($useColumnDelemiter === false)
        {
            if ($this->cachedTableBorder !== '')
            {
                return $this->cachedTableBorder;
            }
            $this->cachedTableBorder =
                "+".str_repeat($borderCharacter, array_sum($this->lengthPerColumn) + 1)."+";
            return $this->cachedTableBorder;
        }


        if ($this->cachedRowBorder !== '')
        {
            return $this->cachedRowBorder;
        }

        $this->cachedRowBorder .= '+';
        foreach ($this->lengthPerColumn as $colLen)
        {
            $this->cachedRowBorder .= str_repeat($borderCharacter, $colLen);
            $this->cachedRowBorder .= '+';
        }

        return $this->cachedRowBorder;

    }

    protected function filterVal(string $value, int $format): string
    {
        if ($this->flags | Table::FLAG_FILTER_TERMINAL_COLORS)
        {

        }

        return $value;
    }

    protected function printRow(array $row, int $format) : string
    {
        $result = '';
        $i = 0;
        foreach ($row as $column)
        {
            if ($i === 2)
            {
                $j = 1;
            }
            $value = $this->formatCell($column, $format, $i);
            if ($i === 0)
            {
                $result .= "|";
            }
            $result .= "$value|";
            $i++;

        }
        return $result;
    }

    public function out(int $headerFormat = Table::ALIGN_LEFT, int $rowsFormat = Table::ALIGN_LEFT): string
    {
        $this->calcMaxLenPerRow();
        $result = '';

        $result .= $this->outBorder(Table::BORDER_TYPE_NORMAL);
        $result .= PHP_EOL;
        $result .= $this->printRow($this->header, $headerFormat);
        $result .= PHP_EOL;
        $result .= $this->outBorder(Table::BORDER_TYPE_NORMAL, true);
        $result .= PHP_EOL;

        $result .= '';
        foreach ($this->rows as $row)
        {
            $result .= $this->printRow($row, $rowsFormat);
            $result .= PHP_EOL;
            $result .= $this->outBorder(Table::BORDER_TYPE_NORMAL, true);
            $result .= PHP_EOL;
        }

        return $result;
    }

    public function pushRow(array $row)
    {
        if (count($row) < $this->getColumnCount())
        {
            throw new \ErrorException('Row columns must be <= then header columns');
        }

        $this->rows [] = $row;
        $this->rowsCount++;
    }

    public function getRowByIndex(int $index): array
    {
        if (!array_key_exists($index, $this->rows))
        {
            return $this->rows[$index];
        }

        throw new \Exception('Error on access to row with index '.$index);
    }
}