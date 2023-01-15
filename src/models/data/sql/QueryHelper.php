<?php

declare(strict_types=1);

namespace mmvc\models\data\sql;

interface QueryHelper
{
    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_OUTER = 'OUTER';
    const JOIN_TYPE_FULL = 'FULL';

    /**
     * Генерация запроса на удаление таблицы
     * @param string $table имя таблицы
     * @param string $where фильтрация в формате field=:val, где :val - название ключа в
     * параметре $values без символа :
     * @param array|null $values массив значений в формате ключ-значение, используемый в where
     * @return string запрос на удаление
     */
    public function buildDelete(string $table, string $where, ?array $values = null): string;

    /**
     * Генерация запроса на выборку из таблицы
     * @param string $from имя таблицы
     * @param array $fields массив полей для выборки
     * @param string|null $where фильтрация в формате field=:val, где :val - название ключа в
     * параметре $values без символа :
     * @param array|null $values массив значений в формате ключ-значение, используемый в where
     * @return string запрос на выборку
     */
    public function buildSelect(string $from, array $fields = ['*'], ?string $where = null, ?array $values = null): string;

    /**
     * Генерация запроса на описание структуры таблицы
     * @param string $table
     * @return string запрос на описание структуры таблцы
     */
    public function buildDescribe(string $table) : string;

    /**
     * Генерация запроса на вставку строки в таблицу
     * Создание запроса для добавления записи в базу
     * Вызывается при сохранении (метод save())
     * @param string $table имя таблицы
     * @param array $properties свойства
     * @return string готовый запрос INSERT INTO $tablename ($columns) VALUES ($values);
     */
    public function buildInsert(string $table, array &$properties) : string;

    /**
     * Генерация запроса на обновление записей в таблице
     * @param string $table имя таблицы
     * @param array $values массив значений в формате ключ-значение для обновления
     * @param string|null $where фильтрация в формате field=:val, где :val - название ключа в
     * параметре $values без символа :
     * @param array|null $whereValues массив значений в формате ключ-значение, используемый в where
     * @return string запрос на обновление
     */
    public function buildUpdate(string $table, array $values, ?string $where = null, ?array $whereValues = null) : string;

    /**
     * Генерация подстроки лимита
     * @param string $query строка запроса, полученная в результате выполнения методов build*
     * @param int $limit
     * @param int $offset
     * @return string добавление подстроки лимита
     */
    public function addLimit(string $query, int $limit, int $offset = 0) : string;

    /**
     * Генерация подстроки для ограничения количества записей, к которым применяются изменения
     * @param string $where фильтрация в формате field=:val, где :val - название ключа в
     * параметре $values без символа :
     * @param array|null $values массив значений в формате ключ-значение, используемый в where
     * @return string добавление подстроки для фильтрации
     */
    public function addWhere(string $where, ?array $values = null): string;

    /**
     * Генерация подстроки для соединения таблиц
     * @param string $query строка запроса, полученная в результате выполнения методов build*
     * @param string $type тип соединения (константы JOIN_TYPE_*)
     * @param string $table имя таблицы
     * @param string $on критерий соединения
     * @return string добавление подстроки для соединения
     */
    public function addJoin(string $query, string $type, string $table, string $on): string;
}