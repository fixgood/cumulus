<h1><?=Language::GetText('videos_by')?>: <?=$member->username?></h1>
<p><a href="<?=HOST?>/members/<?=$member->username?>/" title="<?=Language::GetText('go_to_profile')?>"><?=Language::GetText('go_to_profile')?></a></p>


<?php if ($db->Count ($result) > 0): ?>

    <?php while ($row = $db->FetchObj ($result)): ?>

        <?php
        $video = new Video ($row->video_id);
        $rating = new Rating ($row->video_id);
        ?>

        <div class="block">

            <a class="thumb" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>">
                <span class="duration"><?=$video->duration?></span>
                <span class="play-icon"></span>
                <img src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" alt="<?=$video->title?>" />
            </a>

            <a class="large" href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->slug?>" title="<?=$video->title?>"><?=$video->title?></a>
            <p><?=Functions::CutOff ($video->description, 190)?></p>
            <span class="like">+<?=$rating->GetLikeCount()?></span>
            <span class="dislike">-<?=$rating->GetDislikeCount()?></span>
            <br clear="all" />

        </div>

    <?php endwhile; ?>

<?php else: ?>
    <div class="block"><strong><?=Language::GetText('no_member_videos')?></strong></div>
<?php endif; ?>

<?=$pagination->Paginate()?>
