<?php
    require_once('config.php');
    $conf = new Config;
    
    $frame_width = 628.0;
    $frame_height = 422.0;
    $space_width = 72.0;
    $space_height = 32.0;
    
    header("Content-type: text/css; charset: UTF-8");

    ?>

            .floor-frame {
                padding-top: <?php echo 'calc('.$frame_height.' * 100% / '.$frame_width.');'; ?>

            }

            .parking-space {
                width: <?php echo 'calc('.$space_width.' * 100% / '.$frame_width.');'; ?>

                height: <?php echo 'calc('.$space_height.' * 100% / '.$frame_height.');'; ?>
                
            }

    <?php

    $space = [];
    $query = "SELECT `id`, `floor_id`, `name`, `top`, `left` FROM `space_position` WHERE `enabled` = '1';";
    // SELECT JSON_OBJECT('type',`type`,'top',`top`,'left',`left`) FROM `space_position` WHERE 1;
    $result = $conf->mysqli->query($query);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {    ?>
            
            .parking-space<?php echo '#n_'.$row['id']; ?> {
                left: <?php echo 'calc('.$row['left'].'.0 * 100% / '.$frame_width.');'; ?>

                top: <?php echo 'calc('.$row['top'].'.0 * 100% / '.$frame_height.');'; ?>

            }
            <?php


        }
    }

?>

