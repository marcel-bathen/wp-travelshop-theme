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
    }


?>

<pre>
    <?php print_r($args); ?>
</pre>
<section class="content-block content-block-list-active-filters">

</section>