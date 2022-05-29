<?php//code-bookmark This the php page called by the favourite url/*Template Name: Favourite Template/** current-php-code 2020-Oct-10* input-sanitized : id,lang,mode* current-wp-template:  favorites page * current-wp-top-template*/$id = (int)FLInput::get('id');$lang = FLInput::get('lang','en');$mode = FLInput::get('mode','');get_header();global $wpdb;if(!is_user_logged_in()){	 wp_redirect(site_url());}$limit = 12;$offset = (int)0 * $limit;$userId = get_current_user_id();$favContent_raw = get_user_meta($userId,"_favorite_content",true);$favFree_raw = get_user_meta($userId,"_favorite_translator",true);$queryFree = $queryCont = '';$favContent_list = trim($favContent_raw,',');$favFree_list = trim($favFree_raw,',');if ($favFree_list) {    $queryFree =        "SELECT u.ID primary_id,u.user_nicename,'' user_id,u.display_name title,                    (SELECT meta_value FROM `wp_usermeta` WHERE `meta_key` = 'description' and user_id=u.ID) description,                    '' content_sale_type,'0' price,                    'translator' as job_type                  FROM  wp_users u                   INNER JOIN wp_fl_user_data_lookup lookup on u.ID = lookup.user_id                     WHERE u.ID IN($favFree_list)                  GROUP BY u.ID";}if ($favContent_list) {    $queryCont =        "SELECT wlc.content_view,wlc.publish_type,wlc.id primary_id,'' user_nicename,                    wlc.user_id,wlc.content_title title,wlc.content_summary description,wlc.content_sale_type,                    wlc.content_amount price,wlc.content_cover_image image,'content' job_type                   FROM wp_linguist_content wlc                    WHERE (wlc.id IN($favContent_list)) AND wlc.user_id IS NOT NULL GROUP BY wlc.id";}$queryPurchase =    "SELECT wlc.content_view,wlc.publish_type,wlc.id primary_id,'' user_nicename,                    wlc.user_id,wlc.content_title title,wlc.content_summary description,wlc.content_sale_type,                    wlc.content_amount price,wlc.content_cover_image image,'content' job_type                   FROM wp_linguist_content wlc                    WHERE  (wlc.user_id IS NOT NULL AND wlc.publish_type='Purchased' and purchased_by=" . $userId . ") GROUP BY wlc.id";$foundDataArrays = [];if($favContent_list && $favFree_list){    $foundDataArrays[] =  $wpdb->get_results($queryFree,ARRAY_A);    $foundDataArrays[] =  $wpdb->get_results($queryCont,ARRAY_A);} elseif($favContent_list){    $foundDataArrays[] = $wpdb->get_results($queryCont,ARRAY_A);} elseif($favFree_list) {    $foundDataArrays[] = $wpdb->get_results($queryFree,ARRAY_A);}$foundDataArrays[] =  $wpdb->get_results($queryPurchase,ARRAY_A);$foundData = [];foreach ($foundDataArrays as $sub_array) {    foreach ($sub_array as $sub_key => & $sub_val)    if (isset($sub_val['content_sale_type'])) {        if ($sub_val['content_sale_type'] === 'Fixed') {            $sub_val['content_sale_type'] = '';        }    }    $foundData = array_merge($foundData,$sub_array);}//will_dump('found',$foundData);//sort found data by title attributeusort($foundData, function ($item1, $item2) {    return $item1['title'] <=> $item2['title'];});						if($mode && $id)	{		$contentId = $id;		$content_detail = $wpdb->get_row( "select * from wp_linguist_content where user_id IS NOT NULL AND id = $contentId", ARRAY_A );				$chapters = $wpdb->get_results( "select * from wp_linguist_content_chapter where user_id IS NOT NULL AND linguist_content_id = $contentId", ARRAY_A );		$wp_upload_dir  = wp_upload_dir();		$basepath   = $wp_upload_dir['baseurl'];				$pdf = new PDF();				$title = $content_detail['content_title'];		$pdf->SetTitle($title);					$pdf->Description($content_detail['content_summary']);		$pdf->Ln(12);        //code-notes [image-sizing]  content getting small size for unit        $content_cover_image_url = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(            $content_detail['content_cover_image'],FreelinguistSizeImages::SMALL,true);		$html =            '<table>				<tr>					<td><img src="'.$content_cover_image_url.'" ><td>					<td>'.$content_detail['content_summary'].'</td>				</tr>		</table>';		$pdf->Ln(12);		foreach($chapters as $key=>$value){			$chapterNo=$key+1;			$value['content_html']=str_replace("&#39;","'",stripslashes($value['content_html']));			$value['content_html']=str_replace('&quot;','"',stripslashes($value['content_html']));			$pdf->PrintChapter($chapterNo,$value['title'],$value['content_html']);			$pdf->Ln(12);		}				$pdf->Ln(12);				ob_end_clean();		//$pdf->Output();		$pdf->Output('D','content.pdf');		exit;	}	?>    <!-- code-notes removed /js/jquerymin.js (version 1.11.3) --><div class="title-sec">	<div class="container">		<span class="bold-and-blocking large-text">Favorite</span>	</div></div><div class="project-post-cont search-res">	<div class="container">		<div class="search-cont">			<div class="col-md-12">					<div class="search-head hide">					<div class="category-grid">						<ul>							<li><a class="grid-row" href=""></a></li>							<li><a class="grid-view" href=""></a></li>						</ul>					</div>				</div>				<?php 				if($foundData){ 					$upload_dir      = wp_upload_dir();				?>				<div class="search-row">					<ul style="margin-bottom: 10em">					<?php 					foreach($foundData as $jobs){//					    print "<pre>".print_r($jobs,true)."</pre>\n";						$image = get_template_directory_uri().'/images/default-img-400x240.gif';						if($jobs['job_type']=='content'){							$priceLable = 'Price or best price';							$viewLable = 'Num of view';                            //code-notes [image-sizing]  content getting small size for unit                            $image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(                                $jobs['image'],FreelinguistSizeImages::SMALL,true);                            $href = site_url().'/content/?lang=en&mode=view&content_id='.FreelinguistContentHelper::encode_id($jobs['primary_id']);							if ($jobs['content_sale_type']) {                                $priceValue = '$'.$jobs['price'].'/'.$jobs['content_sale_type'];                            } else {                                $priceValue = '$'.$jobs['price'];                            }							$noOfDone = $jobs['content_view'].' View';							$favIds = $favContent_raw;							$country = get_user_meta($jobs['user_id'],'user_residence_country',true);							$country = ($country ?  get_countries()[$country] : '');							$Id=$jobs['primary_id'];							//$jobs['publish_type'];						} elseif($jobs['job_type']=='translator'){                            $Id=$jobs['primary_id'];							$priceLable = 'Hourly Rate';							$viewLable = '# of projects/contests done';							$image = null;							if (array_key_exists('image',$jobs)) {                                $image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($jobs['image'],FreelinguistSizeImages::SMALL,true);                            }							$username = $jobs['user_nicename'];                            $href = site_url()."/user-account/?lang=$lang&profile_type=translator&user=".$username;							$priceValue = '$'.$jobs['price'].'/hourly';							$noOfDone = '';							$favIds = $favFree_raw;							$country = get_user_meta($jobs['primary_id'],'user_residence_country',true);							$country = ($country ?  get_countries()[$country] : '');						} else {						    continue;                        }														?>						<li id="row_<?php echo $Id; ?>" class="favorite-result-item">							<a href="<?php echo $href; ?>">								<div class="search-row-left">                                    <?php if ($image) { ?>									<img src="<?php echo $image; ?>" alt="profile image">                                    <?php } ?>								</div>								<div class="search-row-right">									<span class="bold-and-blocking large-text"><?php echo $jobs['title']; ?> <br> <label><?php echo $country; ?></label></span>									<div class="ratingsec">										<div class="price"><?php echo $priceValue; ?></div>										<div class="price">										<span class="hide">Not yet rated</span>										</div>										<div class="price"><?php echo $noOfDone; ?></div>									</div>									<p><?php echo substr($jobs['description'],0,280); ?></p>									<ul class="option">										<li class="view"><a href="<?php echo $href; ?>"><i class="hide fa fa-eye larger-text" aria-hidden="true"></i></a></li>										<li class="cart active hide"><a href="#"><i class="fa fa-cart-arrow-down  larger-text" aria-hidden="true"></i></a></li>										<?php                                        if ($jobs['job_type'] === 'content') {                                            if (array_key_exists('publish_type', $jobs) && ($jobs['publish_type'] === 'Purchased')) {                                                ?>                                               <?php                                            } else {                                                ?>                                                <li class=" heart active"><a href="#"                                                                                       onclick="return add_favorite_content(<?php echo $Id; ?>)"                                                                                       title="Remove from favorite"><i                                                                class="fa fa-heart  larger-text" aria-hidden="true"></i></a>                                                </li>                                            <?php }                                        } //end if content                                        else {                                            ?>                                            <li class="">                                                <?php                                                set_query_var( 'for_user_id', $Id );                                                get_template_part('includes/user/author-user-info/translator', 'button-favorite');                                                ?>                                            </li>                                            <?php                                        }										?>									</ul>                                    <?php if ($jobs['job_type'] === 'translator') { ?>                                        <div class="hire-freelancer-button-holder">                                            <button class="red-btn-no-hover hire-freelancer"                                                    data-freelancer_nicename="<?= $username ?>"                                                    data-freelancer_id="<?= $Id ?>"                                            >                                                Hire                                            </button>                                        </div>                                    <?php } //end if search item is translator ?>								</div>							</a>						</li>					<?php } ?>					</ul>				</div>				<?php } else { ?>				<div class="search-row">					No results found!				</div>				<?php } ?>			</div>		</div>	</div></div><style>.search-row ul li .search-row-left{	width:20%;}</style><!--suppress JSValidateTypes -->    <script type="text/javascript">		function showalertmessage(msg){		 bootbox.alert(msg);		return false;	}</script><?php get_template_part('includes/user/author-user-info/translator', 'hire-dialog'); ?><?php get_footer('homepagenew'); ?>