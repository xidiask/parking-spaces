<?php
require_once('config.php');
require_once('icon.php');
$conf = new Config;
$icon = new Icon;


//$floor = ['-3','-2','-1','0','1','2','3','4','5','6','7'];
$plate = ['ION4400','IMM1492','XNN8998','IYM8315','IZO1743','IOH9300','XNN1699','XNI1026','XNX9369','YNY4428','XNT5226','HKT2392','IMK6725','IHK4726','XNN7733','XNT6457','PEH8295','TYT431','XNI9464','XNP9546','PMN485','HKZ7622','XNX2324','XNI4058','ZXH8522','IPA5341','MYZ7892','XNK4092','XNI9996','XNP8218','XN03305'];


    $floor = [];
    $query = "  SELECT `f`.`id`, `f`.`name`, `f`.`label`, GROUP_CONCAT(`s`.`id`) `space_ids`, GROUP_CONCAT(`s`.`name`) `spaces`, GROUP_CONCAT(`s`.`type`) `type`
                FROM `floor` `f`
                LEFT JOIN `space_position` `s`
                ON `f`.`id` = `s`.`floor_id` AND `s`.`enabled` = '1'
                WHERE `f`.`enabled` = '1'
                GROUP BY `f`.`id`;";
                // SELECT JSON_OBJECT('type',`type`,'top',`top`,'left',`left`) FROM `space_position` WHERE 1;
    $result = $conf->mysqli->query($query);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $floor[$row['id']] = [  'name'=>$row['name'],
                                    'label'=>$row['label'],
                                    'spaces'=>array_combine(explode(',',$row['space_ids']),explode(',',$row['spaces'])),
                                    'space_types'=>array_combine(explode(',',$row['space_ids']),explode(',',$row['type']))
                                ];
        }
    }
    $selected_floor = $_COOKIE['parking-spaces-floor'] ?? array_key_first($floor);

    //echo $query;
	//echo '<pre>';print_r($floor);echo '</pre>';


$conf->header();   ?>



<div class="container">
    <div class="mt-5 d-flex flex-column">
        <div class="tab-content" id="pills-floorContent">
            <div class="input-move-to d-none">
                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" >
                    <div class="input-group mb-3">
                        <div class="input-field my-0">
                            <input type="text" id="" class="form-control" >
                            <label for="" class="form-label">Θέση</label>
                            <span class="error is-not-unique">Η θέση χρησιμοποιείται ήδη.</span>
                        </div>
                        <button type="submit" class="btn btn-outline-secondary" type="button" id="button-addon2">
                            <?php $icon->svg('caret-right-fill'); ?>
                        </button>
                    </div>
                </form>
            </div>
            <?php
            foreach ($floor as $floor_id => $row) {
                $selected = $selected_floor == $floor_id ; ?>
                <div class="<?php echo 'tab-pane fade'.($selected?' active show':''); ?>" id="<?php echo 'floor_id_'.$floor_id; ?>" role="tabpanel" aria-labelledby="<?php echo 'tab_floor_id_'.$floor_id; ?>">
                    <div class="">
                        <div class="text-center">
                            <?php echo $row['label']; ?>
                        </div>
                        <div class="floor-frame" >   <?php
                            if(!empty($row['spaces'])) {
                                foreach ($row['spaces'] as $space_id => $space_name) {
                                    $type = $row['space_types'][$space_id] == 'temp' ? ' temp':'';
                                    $occupied_space = $space_id % 2;    ?>
                                    <div class="<?php echo 'parking-space'.$type.($occupied_space?' occupied popover-top':''); ?>" id="<?php echo 'n_'.$space_id; ?>" title="Μετακίνηση στη θέση" >
                                        <div class="space-name">
                                            <?php echo  $space_name; ?>
                                        </div> <?php
                                        if($occupied_space) { ?>
                                            <div class="car-plate">
                                                <?php echo  $plate[array_rand($plate,1)]; ?>
                                            </div> <?php
                                        }   ?>
                                    </div>    <?php
                                }
                            }   ?>
                        </div>
                    </div>
                </div>
                <?php
            }   ?>
        </div>
        <ul class="nav nav-pills mb-3 mx-auto" id="pills-tab" role="tablist">   <?php
            foreach ($floor as $floor_id => $row) {
                $selected = $selected_floor == $floor_id ; ?>
                <li class="nav-item" role="presentation">
                    <button class="<?php echo 'nav-link'.($selected?' active':''); ?>" id="<?php echo 'tab_floor_id_'.$floor_id; ?>" data-floor-id="<?php echo $floor_id; ?>" data-bs-toggle="pill" data-bs-target="<?php echo '#floor_id_'.$floor_id; ?>" type="button" role="tab" aria-controls="pills-contact" aria-selected="<?php echo $selected?'true':'false'; ?>">
                        <?php echo $row['name']; ?>
                    </button>
                </li>   <?php
            }   ?>
        </ul>
    </div>
</div>


<?php
$conf->footer();