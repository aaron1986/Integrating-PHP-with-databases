<?php 
include("inc/functions.php");

$pageTitle = "Personal Media Library";
$section = null;

include("inc/header.php"); ?>

		<div class="section catalog random">
			<div class="wrapper">
            </div>
            <div class="search">
                <form method="get" action="catalog.php">
                    <label for="s">Search: </label>
                    <input type="text" name="s" id="s" />
                    <input type="submit" value="go" />
        </form>
        </div>
				<h2>May we suggest something?</h2>

          
        <ul class="items">
            <?php
            $random = random_catalog_array();
            foreach ($random as $item) {
                echo get_item_html($item);
            }
            ?>							
				</ul>


		</div>

<?php include("inc/footer.php"); ?>