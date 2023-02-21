<?php

if (!defined('ABSPATH')) die;

$has_modal = $args['modal_content'] ?? false;
?>

<!---- bio cards ---->
<div class="bios <?php echo $args['other_class']; ?>">

    <?php foreach ($args['card_info'] as $card_info) : ?>

        <div class="bio-card">
            <?php if ($has_modal)                mti_get_template_part('partials/bio-details-modal-container', null, MTI_Bio::TEMPLATE_DIR, $card_info); ?>
            <?php echo $has_modal ? '<a href="#" class="show_desc">' : ''; ?>

            <div class="bio-card__image">
                <img src="<?php echo $card_info['img'] ? $card_info['img'] : '/wp-content/uploads/2021/07/generic-female-headshot-e1630685928108.png'; ?>" alt="<?php echo $card_info['title'] ?>" />
            </div>

            <div class="bio-card__info">
                <h3 class="bio-card__name"><?php echo $card_info['title'] ?></h3>
                <?php if (isset($card_info['job'])) : ?>
                    <p class="bio-card__title"><?php echo $card_info['job'] ?></p>
                <?php endif; ?>
                <div class="bio-card__button">READ BIO</div>
            </div>


            <?php echo $has_modal ? '</a>' : ''; ?>

        </div>

    <?php endforeach; ?>

</div>
<!---- end bio cards ---->