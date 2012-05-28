<?php
namespace hamster;
use \PDO,
    \Naf,
    \Exception;

class Db
{
    /** @var PDO $connection */
    static protected $connection;

    /**
     * @return PDO
     */
    static public function getConnection()
    {
        if (! static::$connection) {
            static::$connection = new PDO(
                'sqlite:'.Naf::config('hamster.path_to_db'),
                null,
                null,
                array(PDO::ATTR_PERSISTENT => true)
            );
        }

        return static::$connection;
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return \PDOStatement
     */
    static function selectSecondsSpentOnActivities($dateFrom, $dateTo)
    {
        $stmt = static::getConnection()->prepare('
              SELECT a.name,
                  date(f.start_time) as date,
                  SUM(strftime("%s", f.end_time) - strftime("%s", f.start_time)) as seconds
              FROM activities as a
              JOIN facts as f ON a.id = f.activity_id
              WHERE f.start_time >= ? AND f.end_time <= ?
              GROUP by name, date
              ');
        $stmt->execute(array($dateFrom, $dateTo));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt;
    }

    static public function selectFactsByDate($date)
    {
        $stmt = static::getConnection()->prepare('
              SELECT a.name, f.start_time, f.end_time, f.id,
                  (strftime("%s", f.end_time) - strftime("%s", f.start_time))/3600.0 as hours_spent
              FROM facts as f
              LEFT JOIN activities as a ON a.id = f.activity_id
              WHERE date(f.start_time) = ?
              ');
        $stmt->execute(array($date));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt;
    }

    /**
     * @param int $factId
     * @return \PDOStatement
     */
    static public function selectTagsOfFact($factId)
    {
        $stmt = static::getConnection()->prepare('
              SELECT t.*
              FROM fact_tags as ft
              LEFT JOIN tags as t ON t.id =  ft.tag_id
              WHERE ft.fact_id = ?
              ');
        $stmt->execute(array($factId));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt;
    }

    /**
     * @param int $factId
     * @param int $tagId
     * @return bool
     */
    static public function isFactTaggedBy($factId, $tagId)
    {
        $stmt = static::getConnection()->prepare('
              SELECT *
              FROM fact_tags as ft
              WHERE ft.fact_id = ? AND ft.tag_id = ?
              LIMIT 1
              ');
        $stmt->execute(array($factId, $tagId));

        return count($stmt->fetchAll()) > 0;
    }

    /**
     * @param $name
     * @return int
     * @throws Exception
     */
    static public function assertTagId($name)
    {
        $tagId = self::selectTagByName($name);
        if (!$tagId) {
            $stmt = static::getConnection()->prepare('insert into tags(name) values(?)');
            $stmt->execute(array($name));
            $tagId = self::selectTagByName($name);
        }

        if (!$tagId) {
            throw new Exception('Error inserting tag');
        }

        return $tagId;
    }

    /**
     * @param string $name
     * @return int|null
     */
    static public function selectTagByName($name)
    {
        $stmt = static::getConnection()->prepare('select id from tags where name=?');
        $stmt->execute(array($name));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $row = $stmt->fetch();

        return $row ? $row['id'] : null;
    }

    /**
     * @param int $factId
     * @param int $tagId
     */
    static public function tagFactBy($factId, $tagId)
    {
        $stmt = static::getConnection()->prepare('insert into fact_tags(fact_id, tag_id) values(?, ?)');
        $stmt->execute(array($factId, $tagId));
    }
}