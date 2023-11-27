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

        if ( ($type !== null && $type === 'expand') ) {
            $expand = true;
            $dataPreview = 'true';
            $dataPreviewClass = '';
        }

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

                if ( $expand && $iterateItems > $preview ) {
                    $dataPreview = 'false';
                    $dataPreviewClass = 'd-none';
                }
                ?>
                <div data-preview="<?php echo $dataPreview; ?>" data-name="<?php echo $item->name; ?>" data-name-lowercase="<?php echo strtolower($item->name); ?>" class="form-check <?php echo $dataPreviewClass; ?> <?php echo $has_childs ? 'has-second-level' : ''; echo $is_open;?>">

                    <input class="form-check-input" type="checkbox"
                           id="<?php echo $uuid; ?>"
                           data-id-parent=""
                           data-id="<?php echo $item->id_item; ?>"
                           data-name="<?php echo $fieldname;?>"
                    <?php echo in_array($item->id_item, $selected) ? 'checked' : '';?>
                            <?php echo !empty($is_open) ? 'disabled' : '';?>
                    >
                    <span>
                        <svg><use href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/phosphor-sprite.svg#check-bold"></use></svg>
                    </span>
                    <label class="form-check-label" for="<?php echo $uuid; ?>">
                        <?php
                        if ( $type === 'stars' ) {
                            echo Template::render(APPLICATION_PATH . '/template-parts/micro-templates/stars.php', [
                                'rating' => floatval($item->name),
                                'name' => $item->name,
                            ]);
                        } else {
                            echo '<span class="form-check-label-inner">' . $item->name . '</span>';
                        }
                        ?>
                        <span class="small">(<?php echo $item->count_in_search; ?>)</span>
                    </label>
                    <?php if ($has_childs === true) { ?>
                        <button type="button" class="toggle-second-level" >
                            <svg><use href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/phosphor-sprite.svg#caret-down-bold"></use></svg>
                        </button>
                        <div class="list-filter-second-level">
                            <?php foreach ($childs[$item->id_item] as $child_item) {
                                $uuid = 'ti-'.uniqid();
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           id="<?php echo $uuid; ?>"
                                           data-id-parent="<?php echo $item->id_item; ?>"
                                           data-id="<?php echo $child_item->id_item; ?>"
                                           data-name="<?php echo $fieldname;?>"
                                        <?php echo in_array($child_item->id_item, $selected) ? 'checked' : '';?>
                                           >
                                    <span>
                                        <svg><use
                                                    href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/phosphor-sprite.svg#check-bold"></use></svg>
                                    </span>
                                    <label class="form-check-label" for="<?php echo $uuid; ?>">
                                        <?php echo $child_item->name; ?>
                                        <span class="small">(<?php echo $child_item->count_in_search; ?>)</span>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <?php
                $iterateItems++;
            }
    }


?>

<pre>
    <?php print_r($args); ?>
</pre>
<section class="content-block content-block-list-active-filters">

</section>