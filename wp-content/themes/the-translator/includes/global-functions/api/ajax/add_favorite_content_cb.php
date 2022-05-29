<?php

add_action( 'wp_ajax_add_favorite_content',  'add_favorite_content_cb'  );
add_action( 'wp_ajax_nopriv_add_favorite_content', 'add_favorite_content_cb'  );

 function add_favorite_content_cb(){
     /*
    * current-php-code 2020-Oct-14
    * ajax-endpoint  add_favorite_content
    * input-sanitized :id
    * public-api
    */
     $id = (int)FLInput::get('id');


    if(is_user_logged_in()){
        $favContent = get_user_meta(get_current_user_id(),"_favorite_content",true);
        $fav = explode(',',$favContent);

        if( !in_array( $id , $fav ) ){

            $favContent = implode(',',array_merge( $fav, array( $id ) ));
            update_user_meta( get_current_user_id(), '_favorite_content',$favContent);

            $resp 	= array( 'success' => true, 'markup' => 'Remove from Favorite', 'text' => 'Added To Favorite list','addclass'=>'fa-heart-o'  );
        }
        else{
            $favContent  = implode(',',array_diff( $fav, array( $id ) ));
            update_user_meta( get_current_user_id(), '_favorite_content',$favContent);

            $resp 	= array( 'success' => true, 'markup' => 'Add to Favorite', 'text' => 'Removed from Favorite list','addclass'=>'fa-heart'  );
        }
    }
    else{
        $resp 	= array( 'success' => false, 'markup' => 'Add to Favorite', 'text' => 'Added To Favorite list','staus'=>'-1'  );
    }
    echo json_encode( $resp );


    wp_die();
}