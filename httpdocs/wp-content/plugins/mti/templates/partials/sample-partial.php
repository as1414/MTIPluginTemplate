<?php

if (!defined('ABSPATH')) die;

$card_info = $args;

?>

<!-- modal pop-up -->
<div class="modal">
    <div class="modal-content">
        <div class="close">&times;</div>
        <div class="modal-description">
            <div class="bio-card">
                <?php if (isset($card_info['img'])) : ?>
                    <div class="bio-card__image">
                        <img src="<?php echo $card_info['img'] ? $card_info['img'] : '/wp-content/uploads/2021/07/generic-female-headshot.png'; ?>" alt="<?php echo $card_info['title'] ?>" />
                    </div>
                <?php endif; ?>
                <div class="bio-card__info">
                    <h1 class="bio-card__name"><?php echo $card_info['title'] ?></h1>
                    <?php if (isset($card_info['job'])) : ?>
                        <p class="bio-card__title"><?php echo $card_info['job'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bio-description">
                <?php echo $card_info['desc'] ?>
            </div>
        </div>
    </div>
</div>
<!-- end modal pop-up -->