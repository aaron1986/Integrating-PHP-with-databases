<?php


function get_catalog_count($category = null, $search = null) {
    $category = strtolower($category);
    include("inc/connection.php");

    try {
        $sql = "SELECT COUNT(media_id) FROM Media";
        if(!empty($search)) {
            $result = $db -> prepare(
                $sql . " WHERE title LIKE ?");
                $result -> bindValue(1, '%'.$search.'%', PDO::PARAM_STR);
        } else if(!empty($category)) {
            $result = $db -> prepare (
            $sql
            . " WHERE LOWER(category) = ?"
        );
        $result -> bindParam(1, $category, PDO::PARAM_STR);
    } else {
        $result = $db -> prepare($sql);
    }
    $result -> execute();
} catch(Exception $e) {
    echo "bad qury";
}
$count = $result -> fetchColumn(0);
return $count;
}



function full_catalog_array($limit = null, $offset = 0) {
    include("inc/connection.php");

    try {
        $sql = "SELECT media_id, title, category, img 
        FROM Media
        ORDER BY
        REPLACE( 
        REPLACE(
        REPLACE(title, 'The ', ''),
        'An ',
        ''
        ),
        'A ',
        ''
        )";
        if(is_integer($limit)) {
            $result = $db -> prepare($sql . " LIMIT ? OFFSET ?");
            $result -> bindParam(1, $limit, PDO::PARAM_INT);
            $result -> bindParam(2, $offset, PDO::PARAM_INT);
        } else {
            $result = $db -> prepare($sql);
        }
        $result -> execute();
    } catch(Exception $e) {
        echo "Results cannot be seen";
        exit;
    }
    $catalog = $result -> fetchAll();
    return $catalog;
}

function category_catalog_array($category, $limit = null, $offset = 0) {
    include("inc/connection.php");
    $category = strtolower($category);
    try {
        $sql = 
        "SELECT media_id, title, category, img 
        FROM Media
        WHERE LOWER(category) = ?
        ORDER BY
        REPLACE( 
        REPLACE(
        REPLACE(title, 'The ', ''),
        'An ',
        ''
        ),
        'A ',
        ''
        )";
           if(is_integer($limit)) {
            $result = $db -> prepare($sql . " LIMIT ? OFFSET ?");
            $result -> bindParam(1,$category, PDO::PARAM_STR);
            $result -> bindParam(2, $limit, PDO::PARAM_INT);
            $result -> bindParam(3, $offset, PDO::PARAM_INT);
        } else {
            $result = $db -> prepare($sql);
            $result -> bindParam(1, $category, PDO::PARAM_STR);
        }
        $result -> execute();
    } catch(Exception $e) {
        echo "Results cannot be seen";
        exit;
    }
    $catalog = $result -> fetchAll();
    return $catalog;
}
function random_catalog_array() {
    include("inc/connection.php");

    try {
        $result = $db -> query(
        "SELECT media_id, title, category, img 
        FROM Media
        ORDER BY RANDOM()
        LIMIT 4");
    } catch(Exception $e) {
        echo "Results cannot be seen";
        exit;
    }
    $catalog = $result -> fetchAll();
    return $catalog;
}



function search_catalog_array($search, $limit = null, $offset = 0) {
    include("connection.php");

    try {
       $sql = "SELECT media_id, title, category,img 
         FROM Media
         WHERE title LIKE ?
         ORDER BY 
         REPLACE(
           REPLACE(
              REPLACE(title,'The ',''),
              'An ',
              ''
           ),
           'A ',
           ''
         )";
       if (is_integer($limit)) {
          $result = $db->prepare($sql . " LIMIT ? OFFSET ?");
         $result->bindValue(1,"%".$search."%",PDO::PARAM_STR);
          $result->bindParam(2,$limit,PDO::PARAM_INT);
          $result->bindParam(3,$offset,PDO::PARAM_INT);
       } else {
         $result = $db->prepare($sql);
         $result->bindValue(1,"%".$search."%",PDO::PARAM_STR);
       }
       $result->execute();
    } catch (Exception $e) {
       echo "Unable to retrieved results";
       exit;
    }

    $catalog = $result->fetchAll();
    return $catalog;
}


function single_item_array($id) {
    include("inc/connection.php");

    try {
        $result = $db -> prepare(
        "SELECT title, category, img, format, year, genre, publisher, isbn 
        FROM Media
        JOIN Genres ON Media.genre_id = Genres.genre_id
        LEFT OUTER JOIN Books ON Media.media_id = Books.media_id
        WHERE Media.media_id = ?"
        );
       $result -> bindParam(1,$id, PDO::PARAM_INT);
       $result -> execute();
    } catch(Exception $e) {
        echo "Results cannot be seen";
        exit;
    }
    $item = $result -> fetch();
    if(empty($item)) return $item;
    try {
        $result = $db -> prepare(
        "SELECT fullname, role
        FROM Media_People
        JOIN People ON Media_People.people_id = People.people_id
        WHERE Media_People.media_id = ?"
        );
       $result -> bindParam(1,$id, PDO::PARAM_INT);
       $result -> execute();
    } catch(Exception $e) {
        echo "Results cannot be seen";
        exit;
    }
    while($row = $result -> fetch(PDO::FETCH_ASSOC)) {
        $item[$row["role"]][] = $row["fullname"];
    }
    return $item;
}


function genre_array($category = null) {
    $category = strtolower($category);
    include("connection.php");
  
    try {
      $sql = "SELECT genre, category"
        . " FROM Genres "
        . " JOIN Genre_Categories "
        . " ON Genres.genre_id = Genre_Categories.genre_id ";
      if (!empty($category)) {
        $result = $db->prepare($sql 
            . " WHERE LOWER(category) = ?"
            . " ORDER BY genre");
        $result->bindParam(1,$category,PDO::PARAM_STR);
      } else {
        $result = $db->prepare($sql . " ORDER BY genre");
      }
      $result->execute();
    } catch (Exception $e) {
      echo "bad query";
    }
    $genres = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $genres[$row["category"]][] = $row["genre"];
    }
    return $genres;
  }
  

function get_item_html($item) {
    $output = "<li><a href='details.php?id="
        . $item['media_id'] . "'><img src='" 
        . $item["img"] . "' alt='" 
        . $item["title"] . "' />" 
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}

function array_category($catalog,$category) {
    $output = array();
    
    foreach ($catalog as $id => $item) {
        if ($category == null OR strtolower($category) == strtolower($item["category"])) {
            $sort = $item["title"];
            $sort = ltrim($sort,"The ");
            $sort = ltrim($sort,"A ");
            $sort = ltrim($sort,"An ");
            $output[$id] = $sort;            
        }
    }
    
    asort($output);
    return array_keys($output);
}