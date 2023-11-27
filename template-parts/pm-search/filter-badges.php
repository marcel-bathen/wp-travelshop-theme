<?php
    echo "hi";

    $activeFilters = [];

    foreach(TS_FILTERS as $filter) {
        $fieldname = $filter['fieldname'];
        $name = $filter['name'];

        $selected = array();
        if(empty($_GET['pm-c'][$fieldname]) === false && preg_match_all("/[a-zA-Z0-9\-]+(?=[,|\+]?)/", $_GET['pm-c'][$fieldname], $matches) > 0){
            $selected = empty($matches[0]) ? array() : $matches[0];
        }

        $childs = [];
        if(!empty($args['categories'][$fieldname][1])){
            foreach ($args['categories'][$fieldname][1] as $item) {
                $childs[$item->id_parent][] = $item;
            }
        }
        $expand = false;
        $dataPreview = '';
        $dataPreviewClass = '';
        $iterateItems = 1;

        if ( isset($args['categories'][$fieldname]) ) {

            foreach ($args['categories'][$fieldname][0] as $item) {
                $uuid = 'ti-'.uniqid();
                $has_childs = !empty($childs[$item->id_item]) && count($childs[$item->id_item]) > 1;
                // open the second level if neccessary
                $is_open = '';
                if(empty($selected) === false && $has_childs === true){
                    foreach ($childs[$item->id_item] as $child_item){
                        if(in_array($child_item->id_item, $selected) === true){
                            $is_open = ' is-open';
                            break;
                        }
                    }
                }
                if ( in_array($item->id_item, $selected) ) {
                    $activeFilters[] = [
                        'uuid' => $uuid,
                        'id' => $item->id_item,
                        'name' => $item->name
                    ]

                ?>

                <?php
                }

                ?>
                <?php if ($has_childs === true) { ?>


                    <?php foreach ($childs[$item->id_item] as $child_item) {
                        if ( in_array($child_item->id_item, $selected) ) {
                        $uuid = 'ti-'.uniqid();
                            $activeFilters[] = [
                                'uuid' => $uuid,
                                'id' => $child_item->id_item,
                                'name' => $child_item->name
                            ]
                        ?>



                    <?php }
                    }?>
                <?php } ?>
                <?php
                $iterateItems++;
            }
        }
    }


?>

<pre>
    <?php print_r($activeFilters); ?>
</pre>
<section class="content-block content-block-list-active-filters">

</section>