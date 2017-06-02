<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Eloquent\PFiles;
use Mail;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //上傳圖片call fun 方法
    //$data ['vImage'] = $this->_uploadFile ( Input::get ( 'img' ) , $path , $CheckUser->iId);

    //上傳圖片用
    public function get_full_url() {
        $https = ! empty ( $_SERVER ['HTTPS'] ) && strcasecmp ( $_SERVER ['HTTPS'], 'on' ) === 0 || ! empty ( $_SERVER ['HTTP_X_FORWARDED_PROTO'] ) && strcasecmp ( $_SERVER ['HTTP_X_FORWARDED_PROTO'], 'http' ) === 0;
        return ($https ? 'http://' : 'http://') . (! empty ( $_SERVER ['REMOTE_USER'] ) ? $_SERVER ['REMOTE_USER'] . '@' : '') . (isset ( $_SERVER ['HTTP_HOST'] ) ? $_SERVER ['HTTP_HOST'] : ($_SERVER ['SERVER_NAME'] . ($https && $_SERVER ['SERVER_PORT'] === 443 || $_SERVER ['SERVER_PORT'] === 80 ? '' : ':' . $_SERVER ['SERVER_PORT']))) . substr ( $_SERVER ['SCRIPT_NAME'], 0, strrpos ( $_SERVER ['SCRIPT_NAME'], '/' ) );
    }

    //上傳圖片用
    public function _uploadFile($filedata, $path , $uid) {
        $img = explode ( ',', $filedata );
        $data = base64_decode ( $img [1] );

        if (! file_exists ( $path )) {
            mkdir ( $path, 0777, true );
        }

        $filename = uniqid () . '.jpg';


        $success = file_put_contents ( $path . $filename, $data );

        if ($success) {
            $data1['iUserId'] = $uid;
            $data1['vFileType'] = filetype( $path . $filename );
            $data1['vFileServer'] = $this->get_full_url () . "/";
            $data1['vFilePath'] = $path;
            $data1['vFileName'] = $filename;
            $data1['vFileSize'] = filesize( $path . $filename);
            $data1['iCreateTime'] = time();

            $fileid = DB::table('files')->insertGetId($data1);

            return $fileid;
        }else{
            $error = 'error';
            return $error;
        }
    }


    //上傳影片用
    public function _uploadVideo($filedata, $path , $uid) {
        $video = explode ( ',', $filedata );
        $data = base64_decode ( $video [1] );

        if (! file_exists ( $path )) {
            mkdir ( $path, 0777, true );
        }

        $filename = uniqid () . '.mp4';


        $success = file_put_contents ( $path . $filename, $data );

        if ($success) {
            $data1['iUserId'] = $uid;
            $data1['vFileType'] = filetype( $path . $filename );
            $data1['vFileServer'] = $this->get_full_url () . "/";
            $data1['vFilePath'] = $path;
            $data1['vFileName'] = $filename;
            $data1['vFileSize'] = filesize( $path . $filename);
            $data1['iCreateTime'] = time();

            $fileid = DB::table('files')->insertGetId($data1);

            return $fileid;
        }else{
            $error = 'error';
            return $error;
        }
    }

    public function push($sid,$gid,$type,$rid) {
        $data['iSendUserId'] = $sid;
        $data['iGetUserId'] = $gid;
        $data['vType'] = $type;
        $data['iRelateId'] = $rid;
        $data['iCreateTime'] = time();
        $data['iUpdateTime'] = time();
        if($type == 1 ){
            $data ['content'] = '喜歡你的貼文';
        }else if($type == 2 ){
            $data ['content'] = '覺得你的留言很讚';
        }else if($type ==3 ) {
            $data ['content'] = '向你寄送了好友邀請';
        }else {
            $data ['content'] = '傳送了一則訊息給你';
        }

        DB::table('pushs')->insert($data);

        return 1;
    }

    function getBell() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();



        $map1 ['iGetUserId'] = $checkuser->iId;
        $map1 ['bCheck'] = 0;
        $map1 ['bDel'] = 0;

        $notreadcount = DB::table('pushs')->where($map1)->count();

        $map2 ['iGetUserId'] = $checkuser->iId;
        $map2 ['bDel'] = 0;

        $allpush= DB::table('pushs')->where($map2)->orderBy('iUpdateTime', 'DESC')->get();


        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '通知索取成功';
        $this->rtndata ['notreadcount'] = $notreadcount;
        $this->rtndata ['allpush'] = $allpush;
        return response () -> json ( $this->rtndata );
    }

    function readBell () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();



        $map1 ['iGetUserId'] = $checkuser->iId;
        $map1 ['bCheck'] = 0;
        $map1 ['bDel'] = 0;

        $data ['bCheck'] = 1;
        DB::table('pushs')->where($map1)->update($data);

        $map2 ['iGetUserId'] = $checkuser->iId;
        $map2 ['bDel'] = 0;

        $allpush= DB::table('pushs')->where($map2)->orderBy('iUpdateTime', 'DESC')->get();

        foreach ($allpush as $key => $value) {
            $map3['iId'] = $value->iSendUserId;

            $getSendUser = DB::table('users')->where($map3)->first();

            $allpush[$key]->iSendUserName = $getSendUser->vName;

            $map4['iId'] = $getSendUser->vImage;

            $getImgUrl = DB::table('files')->where($map4)->first();
            if ( $getImgUrl ){
                $allpush[$key]->SendUserImage = $getImgUrl->vFileServer . $getImgUrl->vFilePath . $getImgUrl->vFileName;
            }else{
                $allpush[$key]->SendUserImage = '';
            }

        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '通知索取成功';
        $this->rtndata ['allpush'] = $allpush;
        return response () -> json ( $this->rtndata );
    }

    function Register() {
    	$phonenum = trim((Input::has ( 'phonenum' )) ? Input::get ( 'phonenum' ) : "");
    	$account = trim((Input::has ( 'account' )) ? Input::get ( 'account' ) : "");
    	$password = trim((Input::has ( 'password' )) ? Input::get ( 'password' ) : "");


      $map ['vAccount'] = $account;

      $checkRegistered = DB::table('users')->where($map)->first();

      if ( $checkRegistered ) {
        $this->rtndata ['status'] = 0;
        $this->rtndata ['message'] = '此帳號已註冊過';
        return response () -> json ( $this->rtndata );
      }
      $data ['vType'] = 1;
    	$data ['vAccount'] = $account;
        $data ['vPassword'] = hash ( 'sha256', md5 ( $password . "redcholove" ) );
    	$data ['vStuNumber'] = $account;
    	$data ['vName'] = '學生';
        $data ['vImage'] = 0;
        $data ['vWallPaper'] = 0;
    	$data ['vClassName'] = '系所';
    	$data ['vGradeNum'] = 4;
    	$data ['vPhone'] = $phonenum;
        $data ['vUserCode'] = md5 ( "redcholove" . $account . time () );
    	$data ['vUserName'] = '點擊創立自己的綽號';
    	$data ['iCreateTime'] = time();
    	$data ['iUpdateTime'] = time();
        $data ['vCreateIP'] = $_SERVER["REMOTE_ADDR"];
        $data ['vLoginIP'] = '';
        DB::table('users')->insert($data);

    	$this->rtndata ['status'] = 1;
    	$this->rtndata ['message'] = $phonenum;
    	return response() -> json ( $this->rtndata );
    }

    function Login () {
    	$account = trim((Input::has ( 'account' )) ? Input::get ( 'account' ) : "");
        $password = trim((Input::has ( 'password' )) ? Input::get ( 'password' ) : "");

        $map ['vAccount'] = $account;
        $map ['vPassword'] = hash ( 'sha256', md5 ( $password . "redcholove" ) );
        $CheckUser = DB::table('users')->where( $map )->first();

        if( !$CheckUser ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '帳號或密碼錯誤';
            return response ()->json( $this->rtndata );
        }

        $map2 ['bDel'] = 0;

        $newsfeed = DB::table('posts')->where($map2)->orderBy('iUpdateTime', 'DESC')->get();

        foreach ($newsfeed as $key => $value) {
            $map3 ['iId'] = $value->iUserId;
            $getUserId = DB::table('users')->where($map3)->first();

            if( $getUserId ){
                $newsfeed[$key]->postUserId = $getUserId->iId;
                $newsfeed[$key]->iUserId = $getUserId->vName;

                $map4 ['iId'] = $getUserId->vImage;

                $checkimg = DB::table('files')->where($map4)->first();

                if( $checkimg ) {
                    $newsfeed[$key]->userImg = $checkimg->vFileServer . $checkimg->vFilePath . $checkimg->vFileName;
                }else{
                    $newsfeed[$key]->userImg = '';
                }
            }

            $map5 ['iId'] = $value->vImage;
            $getImgUrl = DB::table('files')->where($map5)->first();

            if( $getImgUrl ){
                $newsfeed[$key]->vImage = $getImgUrl->vFileServer . $getImgUrl->vFilePath . $getImgUrl->vFileName;
            }else {
                $newsfeed[$key]->vImage = '';
            }
            $newsfeed[$key]->iCreateTime = date ( 'Y/m/d H:m:s', $value->iCreateTime );

            $map6 ['iPostId'] = $value->iId;
            $map6 ['iUserId'] = $CheckUser->iId;
            $map6 ['bDel'] = 0;

            $checkclicklove = DB::table('loves')->where($map6)->first();

            if( $checkclicklove ) {
                $newsfeed[$key]->clicklove = 1;
            }else{
                $newsfeed[$key]->clicklove = 0;
            }

            $map7 ['iId'] = $value->vVideo;

            $getVideoUrl = DB::table('files')->where($map7)->first();

            if( $getVideoUrl ){
                $newsfeed[$key]->vVideo = $getVideoUrl->vFileServer . $getVideoUrl->vFilePath . $getVideoUrl->vFileName;
            }else{
                $newsfeed[$key]->vVideo = '';
            }
        }
        $this->rtndata['forumname'] = '全部';
        $this->rtndata['uId'] = $CheckUser->iId;
        $this->rtndata['status'] = 1;
        $this->rtndata['message'] = '登入成功';
        $this->rtndata['post'] = $newsfeed;
        $this->rtndata['info'] = $CheckUser->vUserCode;
        return response ()->json( $this->rtndata);
    }

    function OtherPage(){
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iId'] = $checkuser->vImage;
        $checkimg = DB::table('files')->where($map2)->first();
        if( !$checkimg ){
            $checkuser->vImage = '';
        }else{
            $checkuser->vImage = $checkimg->vFileServer . $checkimg->vFilePath . $checkimg->vFileName;
        }



        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '資料搜尋成功';
        $this->rtndata ['info'] = $checkuser;
        return response () -> json ( $this->rtndata );
    }

    function UploadImg() {
        $img = trim((Input::has ( 'img' )) ? Input::get ( 'img' ) : "");
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        if( $img ){
            //$path = "upload/PostImg/" . date ( "Ymd" ) . "/";
            $path = 'upload/users/' . $checkuser->iId . '/';
            $data ['vImage'] = $this->_uploadFile ( Input::get ( 'img' ) , $path , $checkuser->iId);
            $data ['iUpdateTime'] = time();

            $changeImg = DB::table('users')->where($map)->update($data);

            if( !$changeImg ){
                $this->rtndata ['status'] = 0;
                $this->rtndata ['message'] = '上傳失敗,請在傳送一次';
                return response ()-> json ( $this->rtndata );
            }

            $this->rtndata ['status'] = 1;
            $this->rtndata ['vImage'] = $data['vImage'];
            $this->rtndata ['message'] = '上傳成功';
            return response ()->json ( $this->rtndata );
        }
    }

    function GetForumType(){
        $map ['bDel'] = 0;
        $GetGroupType = DB::table('forum_types')->where( $map )->get();

        foreach ( $GetGroupType as $key => $value) {
            $map2 ['vType'] = $value->iId;
            $map2 ['bDel'] = 0;
            $GetTypeDetail = DB::table('forum_types_meta')->where( $map2 )->get();
            if( isset($GetTypeDetail) ){
                $GetGroupType[$key]->detail = $GetTypeDetail;
            }else{
                $GetGroupType[$key]->detail = $GetTypeDetail;
            }

        }

        $this->rtndata['status'] = 1;
        $this->rtndata['info'] = $GetGroupType;
        $this->rtndata['message'] = '搜尋成功';
        return response ()->json( $this->rtndata );
    }

    function PostChooseForum () {
        $map ['bDel'] = 0;
        $GetGroupTypeMeta = DB::table('forum_types_meta')->where($map)->get();

        $this->rtndata ['status'] = 1;
        $this->rtndata ['info'] = $GetGroupTypeMeta;
        $this->rtndata ['message'] = '搜尋成功';
        return response () -> json ( $this->rtndata );
    }

    function PostNewsfeed () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $forum = trim((Input::has ( 'forum' )) ? Input::get ( 'forum' ) : "");
        $content = trim((Input::has ( 'content' )) ? Input::get ( 'content' ) : "");
        $img = trim((Input::has ( 'img' )) ? Input::get ( 'img' ) : "");

        ///測試用
        $video = trim((Input::has ( 'video' )) ? Input::get ( 'video' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }
        $data ['vType'] = $forum;
        $data ['vPostContent'] = $content;
        $data ['iUserId'] = $checkuser->iId;


        if( $img != '' ){
            $path = 'upload/posts/' . $forum . '/';
            $data ['vImage'] = $this->_uploadFile ( Input::get ( 'img' ) , $path , $checkuser->iId);
        }

        if( $video != '' ){
            $path = 'upload/post/' .$forum . '/';
            $data ['vVideo'] = $this->_uploadVideo ( Input::get ( 'video') , $path, $checkuser->iId);
        }

        $data ['iCreateTime'] = time();
        $data ['iCreateDate'] = strtotime(date("Y/m/d"));
        $data ['iUpdateTime'] = time();

        DB::table('posts')->insert($data);

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '貼文成功';
        return response () -> json ( $this->rtndata );
    }

    function GetNewsfeed() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $forum = trim((Input::has ( 'forum' )) ? Input::get ( 'forum' ) : "");
        $type = trim((Input::has ( 'type' )) ? Input::get ( 'type' ) : "");
        //type 1->new 2->hot


        $map ['vUserCode'] = $ac;
        $CheckUser = DB::table('users')->where($map)->first();


        $map2 ['bDel'] = 0;
        if ( $forum == 0 ){

            if ( $type == 2 ){
                $newsfeed = DB::table('posts')->where($map2)->orderBy('bLove', 'DESC')->get();
            }else{
                $newsfeed = DB::table('posts')->where($map2)->orderBy('iUpdateTime', 'DESC')->get();
            }

            $this->rtndata ['forumname'] = '全部';
        }else{
            $map2 ['vType'] = $forum;

            if ( $type == 2 ){
                $newsfeed = DB::table('posts')->where($map2)->orderBy('bLove', 'DESC')->get();
            }else{
                $newsfeed = DB::table('posts')->where($map2)->orderBy('iUpdateTime', 'DESC')->get();
            }

            $map7 ['iId'] = $forum;

            $GetForumName = DB::table('forum_types_meta')->where($map7)->first();
            $this->rtndata ['forumname'] = $GetForumName->vName;
        }



        foreach ($newsfeed as $key => $value) {
            $map3 ['iId'] = $value->iUserId;
            $getUserId = DB::table('users')->where($map3)->first();

            if( $getUserId ){
                $newsfeed[$key]->iUserId = $getUserId->vName;
                $newsfeed[$key]->postUserId = $getUserId->iId;

                $map4 ['iId'] = $getUserId->vImage;

                $checkimg = DB::table('files')->where($map4)->first();

                if( $checkimg ) {
                    $newsfeed[$key]->userImg = $checkimg->vFileServer . $checkimg->vFilePath . $checkimg->vFileName;
                }else{
                    $newsfeed[$key]->userImg = '';
                }
            }

            $map5 ['iId'] = $value->vImage;
            $getImgUrl = DB::table('files')->where($map5)->first();

            if( $getImgUrl ){
                $newsfeed[$key]->vImage = $getImgUrl->vFileServer . $getImgUrl->vFilePath . $getImgUrl->vFileName;
            }else {
                $newsfeed[$key]->vImage = '';
            }
            $newsfeed[$key]->iCreateTime = date ( 'Y/m/d H:m:s', $value->iCreateTime );

            $map6 ['iPostId'] = $value->iId;
            $map6 ['iUserId'] = $CheckUser->iId;
            $map6 ['bDel'] = 0;

            $checkclicklove = DB::table('loves')->where($map6)->first();

            if( $checkclicklove ) {
                $newsfeed[$key]->clicklove = 1;
            }else{
                $newsfeed[$key]->clicklove = 0;
            }

            $map7 ['iId'] = $value->vVideo;

            $getVideoUrl = DB::table('files')->where($map7)->first();

            if( $getVideoUrl ){
                $newsfeed[$key]->vVideo = $getVideoUrl->vFileServer . $getVideoUrl->vFilePath . $getVideoUrl->vFileName;
            }else{
                $newsfeed[$key]->vVideo = '';
            }

            if( $value->vType == 8 || $value->vType == 9 || $value->vType == 12 ){
                $newsfeed[$key]->iUserId = '匿名';
            }
        }
        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '貼文搜尋成功';
        $this->rtndata ['info'] = $newsfeed;
        return response () -> json ( $this->rtndata );
    }

    function CheckCardToday () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();
        //->orderByRaw("RAND()")
        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iPullUserId'] = $checkuser->iId;
        $map2 ['iCreateDate'] = strtotime(date("Y/m/d"));
        $checkpulltoday = DB::table('card_contact')->where($map2)->first();

        if ( !$checkpulltoday ){
            $this->rtndata ['time'] = strtotime(date("Y/m/d"));
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '今天尚未抽卡';
            return response () -> json ( $this->rtndata );
        }

        $map3 ['iId'] = $checkpulltoday->iBePullUserId;
        $getbepull = DB::table('users')->where($map3)->first();

        $map4 ['iId'] = $getbepull->vImage;
        $getImgUrl = DB::table('files')->where($map4)->first();
        if( $getImgUrl ){
            $getbepull->vImage = $getImgUrl->vFileServer . $getImgUrl->vFilePath . $getImgUrl->vFileName;
        }else{
            $getbepull->vImage = 0;
        }

        if ( $checkpulltoday->iUserSent == 1 && $checkpulltoday->iUserRecieve == 1 ) {
            $getbepull->friend = 2;
        }else if( $checkpulltoday->iUserSent == 1 && $checkpulltoday->iUserRecieve == 0 ) {
            $getbepull->friend = 1;
        }else{
            $getbepull->friend = 0;
        }


        $this->rtndata ['status'] = 1;
        $this->rtndata ['info'] = $getbepull;
        $this->rtndata ['message'] = '今天已成功抽過卡';
        return response () -> json ( $this->rtndata );
    }

    function LookWhoPullMe () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $type = trim((Input::has ( 'type' )) ? Input::get ( 'type' ) : "");
        $relateId = trim((Input::has ( 'relateId' )) ? Input::get ( 'relateId' ) : "");

        $map['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map4 ['iPullUserId'] = $relateId;
        $map4 ['iBePullUserId'] = $checkuser->iId;
        $map4 ['iUserSent'] = 1;
        $map4 ['iUserRecieve'] = 0;
        $map4 ['iCreateDate'] = strtotime(date("Y/m/d"));
        if( $type == 3 ) {
            //friendship

            $map2 ['iId'] = $relateId;

            $getWhoPullme = DB::table( 'users' )->where( $map2 )->first();


            $map3 ['iId'] = $getWhoPullme->vImage;

            $getImgUrl = DB::table('files')->where($map3)->first();

            if( $getImgUrl ){
                $getWhoPullme->vImage = $getImgUrl->vFileServer . $getImgUrl->vFilePath . $getImgUrl->vFileName;
            }else{
                $getWhoPullme->vImage = '';
            }


            $this->rtndata ['status'] = 1;
            $this->rtndata ['message'] = '搜尋成功';
            $this->rtndata ['info'] = $getWhoPullme;
            return response () -> json ( $this->rtndata );
        }
        else{
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '還沒寫,87別亂玩';
            return response () -> json ( $this->rtndata );
        }
    }

    function PullCard () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();
        //->orderByRaw("RAND()")
        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $pastcard = [];

        $map2 ['iPullUserId'] = $checkuser->iId;
        $checkpull = DB::table('card_contact')->where($map2)->get();

        foreach ($checkpull as $key => $value) {
            array_push($pastcard, $value->iBePullUserId);
        }

        $map4 ['iBePullUserId'] = $checkuser->iId;
        $checkbepull = DB::table('card_contact')->where($map4)->get();

        foreach ( $checkbepull as $key => $value ){
            array_push($pastcard, $value->iPullUserId);
        }


        $nowcard = DB::table('users')->where('iId','!=',$checkuser->iId)->whereNotIn('iId', $pastcard)->orderByRaw("RAND()")->first();

        if ( !$nowcard ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '帳號不夠抽啦智障,再辦一個新的';
            return response () -> json ( $this->rtndata );
        }
        if( $nowcard->vImage != 0 ){
            $map3 ['iId'] = $nowcard->vImage;

            $checkImg = DB::table('files')->where($map3)->first();

            if( $checkImg ){
                $nowcard->vImage = $checkImg->vFileServer . $checkImg->vFilePath . $checkImg->vFileName;
            }else{
                $nowcard->vImage = 0;
            }
        }

        $data ['iPullUserId'] = $checkuser->iId;
        $data ['iBePullUserId'] = $nowcard->iId;
        $data ['iCreateDate'] = strtotime(date("Y/m/d"));

        DB::table('card_contact')->insert($data);

        $this->rtndata ['status'] = 1;
        $this->rtndata ['info'] = $nowcard;
        $this->rtndata ['message'] = '收取今日卡有成功';
        return response () -> json ( $this->rtndata );
    }

    function Comment() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $comment = trim((Input::has ( 'comment' )) ? Input::get ( 'comment' ) : "");
        $postid = trim((Input::has ( 'postid' )) ? Input::get ( 'postid' ) : "");


        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iId'] = $postid;
        $map2 ['bDel'] = 0;
        $checkpost = DB::table('posts')->where($map2)->first();

        if ( !$checkpost ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '貼文資訊有誤';
            return response () -> json ( $this->rtndata );
        }



        $data ['iPostId'] = $postid;
        $data ['iUserId'] = $checkuser->iId;
        $data ['vCommentContent'] = $comment;
        $data ['iCreateTime'] = time();
        $data ['iUpdateTime'] = time();

        DB::table('comments')->insert($data);


        $data2 ['bComment'] = $checkpost->bComment + 1;

        DB::table('posts')->where($map2)->update($data2);


        if ( $checkuser->iId != $checkpost->iUserId ){
            $this->push ($checkuser->iId,$checkpost->iUserId,1,$checkpost->iId);
        }


        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '留言成功';
        return response () -> json ( $this->rtndata );
    }

    function GetComment() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $postid = trim((Input::has ( 'postid' )) ? Input::get ( 'postid' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2['iPostId'] = $postid;
        $map2['bDel'] = 0;

        $comment = DB::table('comments')->where($map2)->get();

        foreach ($comment as $key => $value) {
            $map3 ['iId'] = $value->iUserId;

            $getuser = DB::table('users')->where($map3)->first();

            $map4 ['iId'] = $getuser->vImage;

            $getImgUrl = DB::table('files')->where($map4)->first();

            if( $getImgUrl ){
                $comment[$key]->UserImage = $getImgUrl->vFileServer . $getImgUrl->vFilePath . $getImgUrl->vFileName;
            }else{
                $comment[$key]->UserImage = '';
            }

            $comment[$key]->iUserId = $getuser->vName;

            $comment[$key]->iCreateTime = date ( 'Y/m/d H:m:s', $value->iCreateTime );
        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['info'] = $comment;
        $this->rtndata ['message'] = '留言搜尋成功';
        return response () -> json ( $this->rtndata );
    }

    function PostLove() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $postid = trim((Input::has ( 'postid' )) ? Input::get ( 'postid' ) : "");
        $love = trim((Input::has ( 'love' )) ? Input::get ( 'love' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iId'] = $postid;
        $map2 ['bDel'] = 0;
        $checkpost = DB::table('posts')->where($map2)->first();

        if ( !$checkpost ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '貼文資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map3 ['iPostId'] = $postid;
        $map3 ['iUserId'] = $checkuser->iId;
        $map3 ['bDel'] = 0;

        $checklove = DB::table('loves')->where($map3)->first();

        if( $love == 0 ){
            if ( $checklove ) {
                $data ['bDel'] = 1;
                DB::table('loves')->where($map3)->update($data);


                $data2 ['bLove'] = $checkpost->bLove - 1;
                DB::table('posts')->where($map2)->update($data2);

                if ( $checkuser->iId != $checkpost->iUserId ){
                    $this->push ($checkuser->iId,$checkpost->iUserId,1,$checkpost->iId);
                }
                $this->rtndata ['status'] = 1;
                $this->rtndata ['info'] = $data2;
                $this->rtndata ['message'] = 'ok';
                return response () -> json ( $this->rtndata );
            }else{
                $this->rtndata ['status'] = 0;
                $this->rtndata ['message'] = '似乎有誤';
                return response () -> json ( $this->rtndata );
            }

        }else if ( $love == 1 ){
            if ( $checklove ) {
                $this->rtndata ['status'] = 0;
                $this->rtndata ['message'] = '似乎有誤';
                return response () -> json ( $this->rtndata );
            }else{
                $data ['iPostId'] = $postid;
                $data ['iUserId'] = $checkuser->iId;
                $data ['iCreateTime'] = time();
                $data ['iUpdateTime'] = time();
                $data ['bDel'] = 0;
                DB::table('loves')->insert($data);


                $data2 ['bLove'] = $checkpost->bLove + 1;
                DB::table('posts')->where($map2)->update($data2);

                if ( $checkuser->iId != $checkpost->iUserId ){
                    $this->push ($checkuser->iId,$checkpost->iUserId,1,$checkpost->iId);
                }
                $this->rtndata ['info'] = $data2;
                $this->rtndata ['status'] = 1;
                $this->rtndata ['message'] = 'ok';
                return response () -> json ( $this->rtndata );
            }
        }else{
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '似乎有誤';
            return response () -> json ( $this->rtndata );
        }
    }

    function SendInvite() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $sendid = trim((Input::has ( 'sendid' )) ? Input::get ( 'sendid' ) : "");

        $map ['vUserCode'] = $ac;

        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ) {
             $this->rtndata['status'] = 0;
             $this->rtndata['message'] = '使用者資訊有誤';
             return response () -> json ( $this->rtndata );
        }


        $map1['iPullUserId'] = $checkuser->iId;
        $map1['iBePullUserId'] = $sendid;

        $checkcard = DB::table('card_contact')->where($map1)->first();

        if ( !$checkcard ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '卡片資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $data ['iUserSent'] = 1;
        DB::table('card_contact')->where($map1)->update($data);
        $hi = $this->push ($checkuser->iId,$sendid,3,$checkcard->iId);

        $this->rtndata['status'] = 1;
        $this->rtndata ['message'] = '發送成功';
        return response () -> json ( $this->rtndata );
    }

    function AcceptInvite() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        ///接受發送的人的id
        $sentuser = trim((Input::has ( 'sentuser' )) ? Input::get ( 'sentuser' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();
        $map2 ['iId'] = $sentuser;
        $checksentuser = DB::table('users')->where($map2)->first();
        if( !$checkuser && !$checksentuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map3 ['iPullUserId'] = $sentuser;
        $map3 ['iBePullUserId'] = $checkuser->iId;
        $map3 ['iUserSent'] = 1;
        $map3 ['iUserRecieve'] = 0;
        $map3 ['iCreateDate'] = strtotime(date("Y/m/d"));

        $checkcontact = DB::table('card_contact')->where($map3)->first();

        if ( !$checkcontact ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '交友資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $data ['iUserRecieve'] = 1;
        DB::table('card_contact')->where($map3)->update($data);


        $data2 ['iUserId'] = $sentuser;
        $data2 ['iUserId2'] = $checkuser->iId;
        $data2 ['iCreateTime'] = time();
        $data2 ['iUpdateTime'] = time();

        DB::table('friends')->insert($data2);

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '確認好友成功';
        return response () -> json ( $this->rtndata );
    }

    function getTitle () {
        $map ['bDel'] = 0;

        $title = DB::table('friendtitle')->where($map)->orderByRaw("RAND()")->first();

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = 'get title success';
        $this->rtndata ['info'] = $title;

        return response () -> json ( $this->rtndata );
    }

    function getFriendList() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }
        $map2 ['iUserId'] = $checkuser->iId;
        $map3 ['iUserId2'] = $checkuser->iId;
        $map2 ['bDel'] = 0;
        $map3 ['bDel'] = 0;
        $getfriend = DB::table('friends')->where($map2)->orWhere($map3)->get();

        foreach ($getfriend as $key => $value) {
            if ( $value->iUserId == $checkuser->iId ){
                //搜尋另一個人
                $map4['iId'] = $value->iUserId2;
                $getdetail = DB::table('users')->where($map4)->first();

                $map6['iId'] = $getdetail->vImage;
                $getimg = DB::table('files')->where($map6)->first();

                if( $getimg ){
                    $getdetail->vImage = $getimg->vFileServer . $getimg->vFilePath . $getimg->vFileName;
                }else{
                    $getdetail->vImage = '';
                }
                $getfriend[$key] = $getdetail;

                $roomUser = $value->iUserId . ',' . $value->iUserId2;
                $roomUser2 = $value->iUserId2 . ',' .$value->iUserId;

                $map8['iUserId'] = $roomUser2;
                $map9['iUserId'] = $roomUser;

                $getRoom = DB::table('room')->where($map8)->orWhere($map9)->first();

                if ( $getRoom ) {
                    $getfriend[$key]->getRoom = $getRoom->iId;
                    $map10 ['iRoomId'] = $getRoom->iId;
                    $lasttalk = DB::table('card_talks')->where($map10)->orderBy('iCreateTime','DESC')->first();
                }else {
                    $getfriend[$key]->getRoom = '';

                }

            }else if( $value->iUserId2 == $checkuser->iId) {
                //搜尋另一個人
                $map5['iId'] = $value->iUserId;
                $getdetail = DB::table('users')->where($map5)->first();


                $map7['iId'] = $getdetail->vImage;
                $getimg = DB::table('files')->where($map7)->first();

                if( $getimg ){
                    $getdetail->vImage = $getimg->vFileServer . $getimg->vFilePath . $getimg->vFileName;
                }else{
                    $getdetail->vImage = '';
                }

                $getfriend[$key] = $getdetail;

                $roomUser = $value->iUserId . ',' . $value->iUserId2;
                $roomUser2 = $value->iUserId2 . ',' .$value->iUserId;

                $map8['iUserId'] = $roomUser2;
                $map9['iUserId'] = $roomUser;

                $getRoom = DB::table('room')->where($map8)->orWhere($map9)->first();

                if ( $getRoom ) {
                    $getfriend[$key]->getRoom = $getRoom->iId;
                    $map10 ['iRoomId'] = $getRoom->iId;
                    $lasttalk = DB::table('card_talks')->where($map10)->orderBy('iCreateTime','DESC')->first();
                    $getfriend[$key]->lasttalk = $lasttalk->vContent;
                }else {
                    $getfriend[$key]->getRoom = '';

                }
            }
        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '搜尋成功';
        $this->rtndata ['info'] = $getfriend;

        return response () -> json ( $this->rtndata );
    }

    function createchat() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $sentto = trim((Input::has ( 'sentto' )) ? Input::get ( 'sentto' ) : "");
        $content = trim((Input::has ( 'content' )) ? Input::get ( 'content' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iUserId'] = $checkuser->iId;
        $map2 ['iUserId2'] = $sentto;

        $map3 ['iUserId'] = $sentto;
        $map3 ['iUserId2'] = $checkuser->iId;
        $checkcontact = DB::table('friends')->where($map2)->orwhere($map3)->first();

        if ( !$checkcontact ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '關係有誤';
            return response () -> json ( $this->rtndata );
        }

        $map4 ['iUserId'] = $checkuser->iId . ',' . $sentto;
        $map5 ['iUserId'] = $sentto . ',' . $checkuser->iId;

        $checkroom = DB::table('room')->where($map4)->orWhere($map5)->first();

        if ( !$checkroom ){
            $data ['iUserId'] = $checkuser->iId . ',' . $sentto;
            $data ['iCreateTime'] = time();

            $roomid = DB::table('room')->insertGetId($data);

            $data2 ['iRoomId'] = $roomid;
            $data2 ['iSentUser'] = $checkuser->iId;
            $data2 ['iRecieveUser'] = $sentto;
            $data2 ['vContent'] = $content;
            $data2 ['iCreateTime'] = time();

            DB::table('card_talks')->insert($data2);

            $this->push ($checkuser->iId,$sentto,4,$roomid);
        }else{
            $data3 ['iRoomId'] = $checkroom->iId;
            $data3 ['iSentUser'] = $checkuser->iId;
            $data3 ['iRecieveUser'] = $sentto;
            $data3 ['vContent'] = $content;
            $data3 ['iCreateTime'] = time();

            DB::table('card_talks')->insert($data3);

            $this->push ($checkuser->iId,$sentto,4,$checkroom->iId);
        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '訊息傳送成功';
        return response () -> json ( $this->rtndata );
    }

    function getchatroom() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $sentto = trim((Input::has ( 'sentto' )) ? Input::get ( 'sentto' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if ( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iUserId'] = $checkuser->iId;
        $map2 ['iUserId2'] = $sentto;

        $map3 ['iUserId'] = $sentto;
        $map3 ['iUserId2'] = $checkuser->iId;
        $checkcontact = DB::table('friends')->where($map2)->orwhere($map3)->first();

        if ( !$checkcontact ){
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '關係有誤';
            return response () -> json ( $this->rtndata );
        }

        $map4 ['iUserId'] = $checkuser->iId . ',' . $sentto;
        $map5 ['iUserId'] = $sentto . ',' . $checkuser->iId;

        $checkroom = DB::table('room')->where($map4)->orWhere($map5)->first();

        if( !$checkroom ) {
            $this->rtndata ['status'] = 2;
            $this->rtndata ['message'] = '尚未有對話紀錄';
            return response () -> json ( $this->rtndata );
        }else{
            //$checkroom

            $map6 ['iRoomId'] = $checkroom->iId;
            $map6 ['bDel'] = 0;

            $gettalk = DB::table('card_talks')->where($map6)->get();

            foreach ($gettalk as $key => $value) {

                if ( $value->iSentUser == $checkuser->iId ){
                    $gettalk[$key]->sentby = 1;
                }else{
                    $gettalk[$key]->sentby = 0;
                }

                $map7['iId'] = $value->iSentUser;
                $getSudata = DB::table('users')->where($map7)->first();

                $map9 ['iId'] = $getSudata->vImage;
                $SuImage = DB::table('files')->where($map9)->first();

                if( $SuImage ){
                    $getSudata->vImage = $SuImage->vFileServer . $SuImage->vFilePath . $SuImage->vFileName;
                }else{
                    $getSudata->vImage = '';
                }

                $map8['iId'] = $value->iRecieveUser;
                $getRudata = DB::table('users')->where($map8)->first();

                $map10 ['iId'] = $getRudata->vImage;
                $RuImage = DB::table('files')->where($map10)->first();
                if( $RuImage ){
                    $getRudata->vImage = $RuImage->vFileServer . $RuImage->vFilePath . $RuImage->vFileName;
                }else{
                    $getRudata->vImage = '';
                }

                // $gettalk[$key]->iCreateTime = date ( 'Y/m/d H:m:s', $value->iCreateTime );
                // $gettalk[$key]->iCreateTime = date ( 'H:m:s', $value->iCreateTime );
                $gettalk[$key]->iCreateTime = date ( 'Y/m/d', $value->iCreateTime );
                $gettalk[$key]->iSentUser = $getSudata->vName;
                $gettalk[$key]->iSentUserImage = $getSudata->vImage;
                $gettalk[$key]->iRecieveUser = $getRudata->vName;
                $gettalk[$key]->iRecieveUserImage = $getRudata->vImage;


            }

            $this->rtndata ['status'] = 1;
            $this->rtndata ['message'] = '訊息搜尋成功';
            $this->rtndata ['info'] = $gettalk;
            return response () -> json ( $this->rtndata );
        }
    }

    function getUser () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        if( !$ac ){
            $map['vType'] = 1;
        $getuser = DB::table('users')->where($map)->get();

        foreach ($getuser as $key => $value) {
            $map2 ['iId'] = $value->vImage;

            $getimUrl = DB::table('files')->where($map2)->first();

            if( $getimUrl) {
                $getuser[$key]->vImage = $getimUrl->vFileServer . $getimUrl->vFilePath . $getimUrl->vFileName;
            }else{
                $getuser[$key]->vImage = '';
            }
        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['info'] = $getuser;

        return response () -> json ( $this->rtndata );
        }else{
            $map['iId'] = $ac;
            $getuser = DB::table('users')->where($map)->first();


                $map2 ['iId'] = $getuser->vImage;

                $getimUrl = DB::table('files')->where($map2)->first();

                if( $getimUrl) {
                    $getuser->vImage = $getimUrl->vFileServer . $getimUrl->vFilePath . $getimUrl->vFileName;
                }else{
                    $getuser->vImage = '';
                }


            $this->rtndata ['status'] = 1;
            $this->rtndata ['info'] = $getuser;

            return response () -> json ( $this->rtndata );
        }
    }

    function changeUserName () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $name = trim((Input::has ( 'change' )) ? Input::get ( 'change' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where( $map )->first();

        if( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $data ['vName'] = $name;
        $update = DB::table('users')->where($map)->update($data);

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '修改名稱成功';
        return response () -> json ( $this->rtndata );
    }

    function getClassSchedule () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where( $map )->first();
        if( !$checkuser ) {
            $this->rtndata['status'] = 0;
            $this->rtndata['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iUserId'] = $checkuser->iId;
        $getClassSchedule = DB::table('class_schedule')->where($map2)->get();

        foreach ($getClassSchedule as $key => $value) {
            $map3 ['iId'] = $value->iClassId;

            $getClassName = DB::table('class')->where($map3)->first();
            $getClassSchedule[$key]->vClassname = $getClassName->vClassname;
            $getClassSchedule[$key]->vClassScore = $getClassName->vClassScore;
            $getClassSchedule[$key]->iClassTime = $getClassName->iClassTime;
            $getClassSchedule[$key]->vClassRoom = $getClassName->vClassRoom;
            $getClassSchedule[$key]->vClassScore = $getClassName->vClassScore;

        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '課表搜尋成功';
        $this->rtndata ['info'] = $getClassSchedule;

        return response () -> json ( $this->rtndata );
    }

    function getTestSchedule () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where( $map )->first();
        if( !$checkuser ) {
            $this->rtndata['status'] = 0;
            $this->rtndata['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iUserId'] = $checkuser->iId;
        $getScheduleId = DB::table('user_test')->where($map2)->get();

        foreach ($getScheduleId as $key => $value) {
            $map3['iId'] = $value->iTestId;

            $getTestDetail = DB::table('test_schedule')->where($map3)->first();

            $map4 ['iId'] = $getTestDetail->iClassId;
            $getClassName = DB::table('class')->where($map4)->first();

            // $getTestDetail->iClassId = $getClassName->vName;
            $getScheduleId[$key]->iClassId = $getClassName->vClassname;



            $getScheduleId[$key]->vTestTime = $getTestDetail->vTestTime;
            $getScheduleId[$key]->vClassName = $getTestDetail->iClassId;
            $getScheduleId[$key]->vTestRoom = $getTestDetail->vTestRoom;
        }

        // $this->rtndata ['getClassName'] = $getClassName;
        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = '搜尋成功';
        $this->rtndata ['info'] = $getScheduleId;
        return response () -> json ( $this->rtndata );
    }

    function getTestScore () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where( $map )->first();
        if( !$checkuser ) {
            $this->rtndata['status'] = 0;
            $this->rtndata['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map2['iUserId'] = $checkuser->iId;

        $getScoreList = DB::table('test_score')->where($map2)->get();

        foreach ($getScoreList as $key => $value) {
            $map3 ['iId'] = $value->iClassId;

            $getClassName = DB::table('class')->where($map3)->first();

            $getScoreList[$key]->iClassId = $getClassName->vClassname;
        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = 'success';
        $this->rtndata ['info'] = $getScoreList;
        return response () -> json ( $this->rtndata );
    }

    function SearchHistory () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = 'error';
            return response () -> json ( $this->rtndata );
        }
        $map2 ['iUserId'] = $checkuser->iId;

        $getresult = DB::table('search_history')->where($map2)->orderBy('iCreateTime','DESC')->get();

        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = 'success';
        $this->rtndata ['info'] = $getresult;
        return response () -> json ( $this->rtndata );
    }

    function GetSearchResult () {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $search = trim((Input::has ( 'search' )) ? Input::get ( 'search' ) : "");
        $map3 ['bDel'] = 0;
        $getResult = DB::table('posts')->where('vPostContent','LIKE',"%{$search}%")->where($map3)->get();
        $count = DB::table('posts')->where('vPostContent', 'LIKE', "%{$search}%")->where($map3)->count();

        foreach ($getResult as $key => $value) {
            if ( $value->vImage != 0 ) {
                $map ['iId'] = $value->vImage;
                $geturl = DB::table('files')->where( $map )->first();

                if( $geturl ) {
                    $getResult[$key]->vImage = $geturl->vFileServer . $geturl->vFilePath . $geturl->vFileName;
                }else{
                    $getResult[$key]->vImage = '';
                }
            }else if( $value->vVideo != 0 ){
                $map2 ['iId'] = $value->vVideo;
                $geturl = DB::table('files')->where( $map2 )->first();

                if( $geturl ) {
                    $getResult[$key]->vVideo = $geturl->vFileServer . $geturl->vFilePath . $geturl->vFileName;
                }else{
                    $getResult[$key]->vVideo = '';
                }
            }
        }

        $this->rtndata ['status'] = 1;
        $this->rtndata ['search'] = $search;
        $this->rtndata ['message'] = 'success';
        $this->rtndata ['info'] = $getResult;
        $this->rtndata ['count'] = $count;
        return response () -> json ( $this->rtndata );
    }

    function StoreSearchRecord () {
        $search = trim((Input::has ( 'search' )) ? Input::get ( 'search' ) : "");
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");

        $map100 ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where( $map100 )->first();
        if( !$checkuser ) {
            $this->rtndata['status'] = 0;
            $this->rtndata['message'] = '使用者資訊有誤';
            return response () -> json ( $this->rtndata );
        }

        $map101 ['vSearch'] = $search;
        $map101 ['iUserId'] = $checkuser->iId;

        $checksearchrecord = DB::table('search_history')->where($map101)->first();

        if ( $checksearchrecord ) {
            $data2 ['iCount'] = $checksearchrecord->iCount + 1;
            DB::table('search_history')->where($map101)->update( $data2 );
        }else {
            $data ['iUserId'] = $checkuser->iId;
            $data ['vSearch'] = $search;
            $data ['iCount'] = 0;
            $data ['iCreateTime'] = time();
            $data ['bDel'] = 0;

            DB::table('search_history')->insert($data);
        }



        $getResult = DB::table('posts')->where('vPostContent', 'LIKE', "%{$search}%")->get();

        foreach ($getResult as $key => $value) {
            if ( $value->vImage != 0 ) {
                $map ['iId'] = $value->vImage;
                $geturl = DB::table('files')->where( $map )->first();

                if( $geturl ) {
                    $getResult[$key]->vImage = $geturl->vFileServer . $geturl->vFilePath . $geturl->vFileName;
                }else{
                    $getResult[$key]->vImage = '';
                }
            }else if( $value->vVideo != 0 ){
                $map2 ['iId'] = $value->vVideo;
                $geturl = DB::table('files')->where( $map2 )->first();

                if( $geturl ) {
                    $getResult[$key]->vVideo = $geturl->vFileServer . $geturl->vFilePath . $geturl->vFileName;
                }else{
                    $getResult[$key]->vVideo = '';
                }
            }
            $map3 ['iId'] = $value->iUserId;
            $getUserId = DB::table('users')->where($map3)->first();

            if( $getUserId ){
                $getResult[$key]->iUserId = $getUserId->vName;
                $getResult[$key]->postUserId = $getUserId->iId;
            }
            $getResult[$key]->iCreateTime = date ( 'Y/m/d H:m:s', $value->iCreateTime );

            if( $value->vType == 8 || $value->vType == 9 || $value->vType == 12 ){
                $getResult[$key]->iUserId = '匿名';
            }

            $map4 ['iId'] = $getUserId->vImage;

            $checkimg = DB::table('files')->where($map4)->first();

            if( $checkimg ) {
                $getResult[$key]->userImg = $checkimg->vFileServer . $checkimg->vFilePath . $checkimg->vFileName;
            }else{
                $getResult[$key]->userImg = '';
            }

            $map7 ['iId'] = $value->vVideo;

            $getVideoUrl = DB::table('files')->where($map7)->first();

            if( $getVideoUrl ){
                $getResult[$key]->vVideo = $getVideoUrl->vFileServer . $getVideoUrl->vFilePath . $getVideoUrl->vFileName;
            }else{
                $getResult[$key]->vVideo = '';
            }
        }

        $this->rtndata ['search'] = $search;
        $this->rtndata ['info'] = $getResult;
        $this->rtndata ['status'] = 1;
        $this->rtndata ['message'] = 'success';
        return response () -> json ( $this->rtndata );
    }

    function Edit_Post() {
        $ac = trim((Input::has ( 'ac' )) ? Input::get ( 'ac' ) : "");
        $id = trim((Input::has ( 'id' )) ? Input::get ( 'id' ) : "");
        $content = trim((Input::has ( 'content' )) ? Input::get ( 'content' ) : "");
        $delete = trim((Input::has ( 'delete' )) ? Input::get ( 'delete' ) : "");

        $map ['vUserCode'] = $ac;
        $checkuser = DB::table('users')->where($map)->first();

        if( !$checkuser ) {
            $this->rtndata ['status'] = 0;
            $this->rtndata ['message'] = 'error';
            return response () -> json ( $this->rtndata );
        }

        $map2 ['iId'] = $id;
        if ( $delete ) {
            $data ['bDel'] = 1;
            DB::table('posts')->where($map2)->update($data);
            $this->rtndata ['status'] = 1;
            $this->rtndata ['message'] = 'delete success';
            return response () -> json ( $this->rtndata );
        }
    }
}
