<?php
namespace hamster;
use \PDO,
    \Naf;

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
}