<?php
    echo "hi";

    $activeFilters = [];

    // transport types
    if (empty($args['transport_types']) && count($args['transport_types']) > 1) {
        return;
    }

    $selected = [];
    if (empty($_GET['pm-tr']) === false) {
        $selected = BuildSearch::extractTransportTypes($_GET['pm-tr']);
    }

    foreach ($args['transport_types'] as $item) {
        if ( in_array($item->name, $selected) ) {
            $activeFilters[]  = [
                'id' => $item->name,
                'name' => $item->name
            ];
        }
    }

    // categories
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

        if ( isset($args['categories'][$fieldname]) ) {

            foreach ($args['categories'][$fieldname][0] as $item) {
                $has_childs = !empty($childs[$item->id_item]) && count($childs[$item->id_item]) > 1;
                // open the second level if neccessary
                if(empty($selected) === false && $has_childs === true){
                    foreach ($childs[$item->id_item] as $child_item){
                        if(in_array($child_item->id_item, $selected) === true){
                            break;
                        }
                    }
                }
                if ( in_array($item->id_item, $selected) ) {
                    $activeFilters[] = [
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
                            $activeFilters[] = [
                                'id' => $child_item->id_item,
                                'name' => $child_item->name
                            ]
                        ?>



                    <?php }
                    }?>
                <?php } ?>
                <?php
            }
        }
    }


?>

<pre>
    <?php print_r($activeFilters); ?>
</pre>
<section class="content-block content-block-list-active-filters">

</section>