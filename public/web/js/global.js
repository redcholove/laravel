/////global variable
var now_page = '';
var last_page = '';
var api_route = 'http://localhost/spa_tku_app/public/api/';

var main_color = '#FF60AF';
var ForumSession = 0;
var post_page_id = 'post-page-content',
    newsfeed_page_id = 'newsfeed-page-content',
    friends_page_id = 'friends-page-content',
    bell_page_id = 'bell-page-content',
    channel_profile_page_id = 'channel-profile-page-content',
    login_page_id = 'login-page-content',
    register_page_id = 'register-page-content',
    other_page_id = 'other-page-content',
    single_newsfeed_id = 'single-newsfeed-page-content',
    newsfeed_comment_id = 'newsfeed-comment-page-content',
    img_upload_preview_id = 'img-upload-preview-page-content',
    create_channel_page_id = 'create-channel-page-content',
    forum_type_page_id = 'forum-type-page-content',
    friend_list_page_id = 'friend-list-page-content',
    card_friends_page_id = 'card-friends-page-content',
    letter_page_id = 'letter-page-content',
    setting_page_id = 'setting-page-content',
    alert_window_page_id = 'alert-window',
    bottom_bar_page_id = 'bottom-bar',
    class_schedule_page_id = 'class-schedule-page-content',
    search_bar_page_id = 'search-bar-page-content';


////////自定義常用到的object簡化function
///全域function
var Global = {
    set_last_page: function (page) {
        Global.BottomBell();
        last_page = page;
    },
    set_now_page: function (page) {
        Global.BottomBell();
        now_page = page;
    },
    change_bg: function (color) {
        $('body').css('background-color', color);
    },
    change_pg: function (now, next) {
        $('#' + now).hide();
        $('#' + next).show();
    },
    preview: function (input, id) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#' + id).attr('src', e.target.result);
                //console.log(e.target.result);
                $('#' + id).show();
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            console.log('在這');
        }
    },
    preview_page_show: function (input, type = 0) {
        //type 0->使用者大頭貼
        //type 1->channel大頭貼
        //type 2->channel 封面
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#img-upload-preview-show').attr('src', e.target.result);
                $('#img-upload-preview-show').attr('item', type);
            }
            reader.readAsDataURL(input.files[0]);
            Global.change_pg(now_page, img_upload_preview_id);
            Global.set_last_page(now_page);
            Global.set_now_page(img_upload_preview_id);
            $('#bottom-bar').hide();
            console.log('now:' + now_page);
            console.log('last:' + last_page);
        } else {
            console.log('在這');
        }
    },
    preview_page_show_upload: function () {
        var src = $('#img-upload-preview-show').attr('src');
        //console.log( src );

        var toServer = {
            ac: Forever.getItem('ac'),
            img: src
        }
        Ajax.Post('UploadImg', toServer, function (r) {
            if (r.status == 1) {
                var toServer = {
                    ac: Forever.getItem('ac')
                };
                console.log(toServer);
                Ajax.Post('otherpage', toServer, function (r) {
                    if (r.status === 1) {
                        console.log(r);
                        if (r.info.vImage != '') {
                            $('#other-page-content #other-page-profile-img').attr('src', r.info.vImage);
                        }
                        $('#other-page-content #other-page-profile-name').text(r.info.vName);
                    } else {
                        alert(r.message);
                    }
                });
                Global.change_pg(now_page, other_page_id);
                Global.set_last_page(last_page);
                Global.set_now_page(other_page_id);
                console.log('now:' + now_page);
                console.log('last:' + last_page);
                $('#bottom-bar').show();
            } else {
                alert(r.message);
            }
        })
    },
    Content: function (element, index, array) {

    },
    ShowBottom: function () {
        $('#bottom-bar').show();
    },
    HideBottom: function () {
        $('#bottom-bar').hide();
    },
    countSubstr: function (str, substr, isIgnore) {
        var count;
        var reg = "";
        if (isIgnore == true) {
            reg = "/" + substr + "/gi";
        } else {
            reg = "/" + substr + "/g";
        }
        reg = eval(reg);
        if (str.match(reg) == null) {
            count = 0;
        } else {
            count = str.match(reg).length;
        }
        return count;
    },
    AlertShow: function () {
        $('#' + alert_window_page_id).show();
    },
    BottomBell: function () {
        var toServer = {
            ac: Forever.getItem('ac')
        }
        console.log(toServer);
        Ajax.Post('getBell', toServer, function (r) {
            if (r.status === 1) {
                console.log(r);
                $('#' + bottom_bar_page_id + ' #bottom-bar-bell .num').text(r.notreadcount);
            } else {
                console.log(r);
            }
        });
    }
};
//Ajax post和get用
var Ajax = {
    Post: function (url, toSever, success) {
        $.ajax({
            url: api_route + url,
            dataType: 'json',
            method: 'post',
            data: toSever,
            success: success
        });
    },
    Get: function (url, success) {
        $.ajax({
            url: api_route + url,
            dataType: 'json',
            method: 'post',
            success: success
        });
    }
};
//sessionStorage 包裝檢疫
var Forever = {
    setItem: function (name, data) {
        sessionStorage.setItem(name, data);
    },
    getItem: function (name) {
        return sessionStorage.getItem(name);
    },
    removeItem: function (name) {
        sessionStorage.removeItem(name);
    }
};
//LocalStorage 包裝檢疫
var Local = {
    set: function (name, val) {
        window.localStorage.setItem(name, val);
    },
    get: function (name) {
        return window.localStorage.getItem(name);
    },
    remove: function (name) {
        window.localStorage.removeItem(name);
    }
}

/////常用到的api post getnewsfeed
var oftenCaller = {
        GoNewsFeed: function (type = 0) {
            if (type == 2) {
                var toServer = {
                    forum: item,
                    ac: Forever.getItem('ac'),
                    type: 2
                }
            } else {
                var toServer = {
                    forum: ForumSession,
                    ac: Forever.getItem('ac')
                }
            }



            var uId = Local.get('uId') || Forever.getItem('uId');
            console.log(toServer);
            Ajax.Post('GetNewsfeed', toServer, function (r) {
                if (r.status === 1) {
                    var content = '';
                    $('#' + newsfeed_page_id + ' #forum-name').html(r.forumname);

                    function Type(element, index, array) {

                        content += '<div class="single-newsfeed-content"> <div class="channel-profile-content" id="go-to-channel">';

                        if (element.userImg !== '') {
                            content += '<img src="';
                            content += element.userImg;
                            content += '">';
                        } else {
                            content += '<img src="demo/profile_demo.png">';
                        }

                        content += '<font class="channel-name">';
                        content += element.iUserId;
                        content += '</font>';


                        if (element.postUserId == uId) {
                            content += '<span class="post-by-self" onclick="newsfeed_dropdown(';
                            content += element.iId;
                            content += ')"><img src="demo/drop-arrow.png"></span>';
                            content += '<div id="drop';
                            content += element.iId;
                            content += '" class="dropdown-content">';
                            content += '<button onclick="newsfeed_dropdown_edit(';
                            content += element.iId;
                            content += ",";
                            content += "'" + element.vPostContent + "'";
                            content += ')">編輯</button>';
                            content += '<button onclick="newsfeed_dropdown_delete(';
                            content += element.iId;
                            content += ')">刪除</button>';
                            content += '<button class="cancel" onclick="newsfeed_dropdown_cancel(';
                            content += element.iId;
                            content += ')">取消</button>';
                            content += '</div>';
                        } else {
                            console.log('back return: ' + element.postUserId);
                            console.log('local uid: ' + uId);
                        }

                        content += ' <br><font class="create-time">';
                        content += element.iCreateTime;
                        content += '</font> </div>';
                        if (element.vImage === '') {} else {
                            content += '<img src="';
                            content += element.vImage
                            content += '" class="post-img">';
                        }

                        if (element.vVideo === '') {

                        } else {
                            content += '<video controls class="post-video" src="';
                            content += element.vVideo;
                            content += '"></video>';
                        }


                        var substr = Global.countSubstr(element.vPostContent, '<br>', true);
                        if (element.vPostContent.length > 30) {
                            var subcontent = element.vPostContent.substr(0, 19);
                            content += ' <div class="text-content"> <font>';
                            content += subcontent;
                            content += '</font>';
                            content += '<button class="keep-reading">繼續閱讀</button> ';

                        } else if (substr > 4) {
                            //var subcontent = '';
                            content += ' <div class="text-content"> <font>';
                            content += element.vPostContent;
                            content += '</font>';
                            content += '<button class="keep-reading">繼續閱讀</button> ';
                        } else {
                            content += ' <div class="text-content"> <font>';
                            content += element.vPostContent;
                            content += '</font>';
                        }

                        content += '<hr> <div class="client-button"> <button onclick="PostLove(this.id,';
                        content += element.iId;
                        content += ')" id="';
                        content += 'postlove' + element.iId;
                        content += '"';
                        if (element.clicklove == 1) {
                            content += 'click="1"';
                            content += '><img src="demo/heart-icon.png">';
                        } else {
                            content += '><img src="demo/heart-grey-icon.png">';
                        }

                        content += element.bLove;
                        content += '</button>';
                        content += '<button onclick="NewsfeedComment(';
                        content += element.iId;
                        content += ')"><img src="demo/comment-icon.png">';
                        content += element.bComment;
                        content += '</button> ';
                        content += '<button onclick="NewsFeedStorage()"><img src="demo/storage.png">';
                        content += element.bStorage;
                        content += '</button> </div></div></div>';
                    }

                    r.info.forEach(Type);
                    $('#' + newsfeed_page_id + ' .all-newsfeed-content').html(content);


                    Global.change_pg(now_page, newsfeed_page_id);
                    Global.set_last_page(newsfeed_page_id);
                    Global.set_now_page(newsfeed_page_id);
                    Global.ShowBottom();

                } else {
                    alert(r.message);
                }
            });
        }
    }
////////自定義常用到的object簡化function


//自動登入 偵測LocalStorage存在
var AutoLogin = {
    login: function () {
        if (Local.get('ac')) {

            var account = Local.get('account'),
                password = Local.get('password');
            var toServer = {
                account: account,
                password: password
            }

            Ajax.Post('Login', toServer, function (r) {
                if (r.status == 1) {
                    Forever.setItem('ac', r.info);
                    Forever.setItem('uId', r.uId);

                    Local.set('ac', r.info);
                    Local.set('uId', r.uId);
                    Local.set('account', account);
                    Local.set('password', password);
                    var content = '';
                    $('#' + newsfeed_page_id + ' #forum-name').html(r.forumname);

                    var uId = Local.get('uId') || Forever.getItem('uId');


                    function Type(element, index, array) {
                        content += '<div class="single-newsfeed-content"> <div class="channel-profile-content" id="go-to-channel">';

                        if (element.userImg !== '') {
                            content += '<img src="';
                            content += element.userImg;
                            content += '">';
                        } else {
                            content += '<img src="demo/grey-background-square.jpg">';
                        }

                        content += '<font class="channel-name">';
                        content += element.iUserId;
                        content += '</font>';

                        if (element.postUserId == uId) {
                            content += '<span class="post-by-self" onclick="newsfeed_dropdown(';
                            content += element.iId;
                            content += ')"><img src="demo/drop-arrow.png"></span>';
                            content += '<div id="drop';
                            content += element.iId;
                            content += '" class="dropdown-content">';
                            content += '<button onclick="newsfeed_dropdown_edit(';
                            content += element.iId;
                            content += ')">編輯</button>';
                            content += '<button onclick="newsfeed_dropdown_delete(';
                            content += element.iId;
                            content += ')">刪除</button>';
                            content += '<button class="cancel" onclick="newsfeed_dropdown_cancel(';
                            content += element.iId;
                            content += ')">取消</button>';
                            content += '</div>';
                        } else {
                            console.log('back return: ' + element.postUserId);
                            console.log('local uid: ' + uId);
                        }

                        content += '<br><font class="create-time">';
                        content += element.iCreateTime;
                        content += '</font> </div>';
                        if (element.vImage === '') {
                        } else {
                            content += '<img src="';
                            content += element.vImage
                            content += '" class="post-img">';
                        }

                        if (element.vVideo === '') {

                        } else {
                            content += '<video controls class="post-video" src="';
                            content += element.vVideo;
                            content += '"></video>';
                        }


                        var substr = Global.countSubstr(element.vPostContent, '<br>', true);
                        if (element.vPostContent.length > 30) {
                            var subcontent = element.vPostContent.substr(0, 19);
                            content += ' <div class="text-content"> <font>';
                            content += subcontent;
                            content += '</font>';
                            content += '<button class="keep-reading">繼續閱讀</button> ';

                        } else if (substr > 4) {
                            //var subcontent = '';
                            content += ' <div class="text-content"> <font>';
                            content += element.vPostContent;
                            content += '</font>';
                            content += '<button class="keep-reading">繼續閱讀</button> ';
                        } else {
                            content += ' <div class="text-content"> <font>';
                            content += element.vPostContent;
                            content += '</font>';
                        }

                        content += '<hr> <div class="client-button"> <button onclick="PostLove(this.id,';
                        content += element.iId;
                        content += ')" id="';
                        content += 'postlove' + element.iId;
                        content += '"';
                        if (element.clicklove == 1) {
                            content += 'click="1"';
                            content += '><img src="demo/heart-icon.png">';
                        } else {
                            content += '><img src="demo/heart-grey-icon.png">';
                        }

                        content += element.bLove;
                        content += '</button>';
                        content += '<button onclick="NewsfeedComment(';
                        content += element.iId;
                        content += ')"><img src="demo/comment-icon.png">';
                        content += element.bComment;
                        content += '</button> ';
                        content += '<button onclick="NewsFeedStorage()"><img src="demo/storage.png">';
                        content += element.bStorage;
                        content += '</button> </div></div></div>';
                    }

                    r.post.forEach(Type);

                    $('#' + newsfeed_page_id + ' .all-newsfeed-content').html(content);
                    console.log(r);
                    Global.change_bg('white');
                    Global.change_pg(login_page_id, newsfeed_page_id);
                    $('#newsfeed-btn-hottest').css('color', '#FF3399');
                    $('#newsfeed-btn-hottest').css('border-bottom', '4px solid #FF3399');
                    $('#newsfeed-btn-newest').css('border-bottom', '4px solid white');
                    $('#bottom-bar').show();
                    Global.BottomBell();
                    Global.set_now_page(newsfeed_page_id);


                    console.log('now:' + now_page);
                    console.log('last:' + last_page);
                } else {
                    alert(r.message);
                }
            });


        } else {
            console.log("can't find local.ac");
        }
    }
}
//app 載入預先function
AutoLogin.login();

/////點下search_bar時列出使用者個人搜尋紀錄用的 function
function Search(val) {
    if ( val == '' ) {
        return 0;
    }
    var toServer = {
        ac: Forever.getItem('ac') || Local.get('ac'),
        search: val
    }
    Ajax.Post('GetSearchResult', toServer, function (r) {
        if (r.status === 1) {
            if (r.info.count === 0) {

            } else {
                var content = '';

                function Type(element, index, array) {
                    content += '<div class="single-history" onclick="StoreHistory(';
                    content += "'" + element.vPostContent + "'";
                    content += ')">';
                    if (element.vImage) {
                        content += '<div class="img" style="background-image: url(';
                        content += "'";
                        content += element.vImage;
                        content += "'";
                        content += ');"></div>';
                    } else {
                        content += '<div class="img" style="background-image: url(';
                        content += "'";
                        content += 'demo/grey-background-square.jpg';
                        content += "'";
                        content += ');"></div>';
                    }

                    content += '<font>';
                    var text = element.vPostContent.replace(/<br>/g, " ");
                    console.log(text);
                    if (text.length > 25) {
                        content += text.substring(0, 25) + '.....';
                    } else {
                        content += text;
                    }
                    content += '</font>';
                    content += '</div>';
                }
                r.info.forEach(Type);
                $('#' + search_bar_page_id + ' .content').html(content);
            }
        } else {
            alert('r.message');
        }

    })

}
/////輸入書自後跳出來的search_result
function StoreHistory(name) {
    var toServer = {
        ac: Forever.getItem('ac') || Local.get('ac'),
        search : name
    }

    Ajax.Post('StoreSearchRecord', toServer, function (r) {
        console.log(r);
        var uId = Forever.getItem('uId') || Local.get('uId');
        var content = '';
            $('#' + newsfeed_page_id + ' #forum-name').html(r.forumname);

            function Type(element, index, array) {

                content += '<div class="single-newsfeed-content"> <div class="channel-profile-content" id="go-to-channel">';

                if (element.userImg !== '') {
                    content += '<img src="';
                    content += element.userImg;
                    content += '">';
                } else {
                    content += '<img src="demo/profile_demo.png">';
                }

                content += '<font class="channel-name">';
                content += element.iUserId;
                content += '</font>';


                if (element.postUserId == uId) {
                    content += '<span class="post-by-self"><img src="demo/drop-arrow.png"></span>';
                } else {
                    console.log('back return: ' + element.postUserId);
                    console.log('local uid: ' + uId);
                }

                content += ' <br><font class="create-time">';
                content += element.iCreateTime;
                content += '</font> </div>';
                if (element.vImage === 0) {} else {
                    content += '<img src="';
                    content += element.vImage
                    content += '" class="post-img">';
                }

                if (element.vVideo === '') {
                } else {
                    content += '<video controls class="post-video" src="';
                    content += element.vVideo;
                    content += '"></video>';
                }


                var substr = Global.countSubstr(element.vPostContent, '<br>', true);
                if (element.vPostContent.length > 30) {
                    var subcontent = element.vPostContent.substr(0, 19);
                    content += ' <div class="text-content"> <font>';
                    content += subcontent;
                    content += '</font>';
                    content += '<button class="keep-reading">繼續閱讀</button> ';

                } else if (substr > 4) {
                    //var subcontent = '';
                    content += ' <div class="text-content"> <font>';
                    content += element.vPostContent;
                    content += '</font>';
                    content += '<button class="keep-reading">繼續閱讀</button> ';
                } else {
                    content += ' <div class="text-content"> <font>';
                    content += element.vPostContent;
                    content += '</font>';
                }

                content += '<hr> <div class="client-button"> <button onclick="PostLove(this.id,';
                content += element.iId;
                content += ')" id="';
                content += 'postlove' + element.iId;
                content += '"';
                if (element.clicklove == 1) {
                    content += 'click="1"';
                    content += '><img src="demo/heart-icon.png">';
                } else {
                    content += '><img src="demo/heart-grey-icon.png">';
                }

                content += element.bLove;
                content += '</button>';
                content += '<button onclick="NewsfeedComment(';
                content += element.iId;
                content += ')"><img src="demo/comment-icon.png">';
                content += element.bComment;
                content += '</button> ';
                content += '<button onclick="NewsFeedStorage()"><img src="demo/storage.png">';
                content += element.bStorage;
                content += '</button> </div></div></div>';
            }

            r.info.forEach(Type);
            $('#' + newsfeed_page_id + ' .all-newsfeed-content').html(content);


            Global.change_pg(now_page, newsfeed_page_id);
            Global.set_last_page(newsfeed_page_id);
            Global.set_now_page(newsfeed_page_id);
            Global.ShowBottom();
    })
}
/////按下列出的search_result後去call store api的function
function SearchHistory() {
    var toServer = {
        ac: Forever.getItem('ac') || Local.get('ac')
    }

    Ajax.Post('SearchHistory', toServer, function( r ) {
        console.log(r);
        var content = '';

        function Type(element, index, array) {
            content += '<div class="single-history" onclick="StoreHistory(';
            content += "'" + element.vSearch + "'";
            content += ')">';
            content += '<font>';
            var text = element.vSearch.replace(/<br>/g, " ");
            console.log(text);
            if (text.length > 25) {
                content += text.substring(0, 25) + '.....';
            } else {
                content += text;
            }
            content += '</font>';
            content += '</div>';
        }
        r.info.forEach(Type);
        $('#' + search_bar_page_id + ' .content').html(content);
    });
    Global.change_pg(now_page, search_bar_page_id);
    Global.set_last_page(now_page);
    Global.set_now_page(search_bar_page_id);
    console.log('now: ' + now_page);
    console.log('last: ' + last_page);

}
/////newsfeed_page 每篇貼文下的 dropdown function
function newsfeed_dropdown (id) {
    console.log(id + 'drop');
    $('#' + newsfeed_page_id + ' #drop' + id ).show();
}
/////newsfeed_page dropdown的 cancel
function newsfeed_dropdown_cancel (id) {
    $('#' + newsfeed_page_id + ' #drop' + id ).hide();
}
/////未完成 在想怎麼實現前端UI
function newsfeed_dropdown_edit (id, content) {
    $('#' + post_page_id + ' .text-content textarea').val(content);
    Global.change_pg( now_page, post_page_id );
    Global.set_last_page ( now_page );
    Global.set_now_page ( post_page_id );
    console.log('edit: ' +id );
    console.log('edit-content: ' + content);
}
/////刪除貼文function
function newsfeed_dropdown_delete (id) {
    var toServer = {
        ac: Forever.getItem('ac') || Local.get('ac'),
        id: id,
        delete: 1
    }
    Ajax.Post('Edit_Post', toServer, function(r) {
        if ( r.status === 1 ){
            console.log(r);
            oftenCaller.GoNewsFeed();
        }else {
            console.log(r);
        }
    })
}

///////////整合過的各頁面event listener
/////bottom-bar  page event
var BottomBar_bar = {
        BottomGoNewsfeed: function () {
            oftenCaller.GoNewsFeed();
        },
        BottomGoFriends: function () {
            var toServer = {
                ac: Forever.getItem('ac')
            }
            Ajax.Post('CheckCardToday', toServer, function (r) {
                if (r.status === 1) {
                    var content = '';
                    content += '<div class="wallpaper"> ';
                    content += '<img src="demo/grey-wallpaper-15.jpg">';
                    content += '</div><div class="profile"> ';
                    if (r.info.vImage === 0) {
                        content += '<img src="demo/grey-background-square.jpg">';
                    } else {
                        content += '<img src="';
                        content += r.info.vImage;
                        content += '">';
                    }

                    content += '</div><div class="name"> <p>';
                    content += r.info.vName;
                    content += '</p></div>';
                    content += '<div class="review"><p>自我介紹區自我介紹區自我介紹區自我介紹區自我介紹區自我介紹區</p></div>';

                    if (r.info.friend == 2) {
                        content += '<div class="sendinvite"><p>你們已經成為好友<p/></div>';
                    } else if (r.info.friend == 1) {
                        content += '<div class="sendinvite"><p>你已送出邀請<p/></div>';
                    } else {
                        content += '<div class="sendinvite"> <button onclick="PullCard(' + r.info.iId + ')"> 向對方送出邀請 </button> </div>';
                    }
                    $('#card-friends-page-content .change').html(content);

                    Global.change_pg(now_page, card_friends_page_id);
                    Global.set_last_page(now_page);
                    Global.set_now_page(card_friends_page_id);
                    console.log('now:' + now_page);
                    console.log('last:' + last_page);
                } else {
                    var toServer = {

                    }
                    Ajax.Post('getTitle', toServer, function (r) {
                        if (r.status === 1) {
                            var content = r.info.name;
                            $('#' + friends_page_id + ' .content p').text(content);
                            Global.change_pg(now_page, friends_page_id);
                            Global.set_last_page(now_page);
                            Global.set_now_page(friends_page_id);

                            console.log('now:' + now_page);
                            console.log('last:' + last_page);
                        } else {

                        }
                    })

                }
            });
        },
        BottomGoOther: function () {
            var toServer = {
                ac: Forever.getItem('ac')
            };
            Ajax.Post('otherpage', toServer, function (r) {
                if (r.status === 1) {
                    if (r.info.vImage != '') {
                        $('#other-page-content #other-page-profile-img').attr('src', r.info.vImage);
                    }
                    $('#other-page-content #other-page-profile-name').text(r.info.vName);
                } else {
                    alert(r.message);
                }
            });
            Global.change_pg(now_page, other_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(other_page_id);
            console.log('now:' + now_page);
            console.log('last:' + last_page);
        },
        BottomGoPost: function () {
            Global.change_pg(now_page, post_page_id);
            Global.HideBottom();
            Global.set_last_page(now_page);
            Global.set_now_page(post_page_id);
            console.log('now:' + now_page);
            console.log('last:' + last_page);
        },
        BottomGoBell: function () {
            var toServer = {
                ac: Forever.getItem('ac')
            }
            Ajax.Post('readBell', toServer, function (r) {
                if (r.status === 1) {
                    $('#' + bottom_bar_page_id + ' #bottom-bar-bell').html('<img src="demo/bell-icon.png"><br>通知');
                    var content = '';

                    function Type(element, index, array) {
                        content += '<div class="single-bell-content" onclick="LookFriendShip(';
                        content += element.vType;
                        content += ',';
                        content += element.iSendUserId;
                        content += ')"> ';

                        if (element.SendUserImage === '') {
                            content += '<img src="demo/grey-background-square.jpg">';
                        } else {
                            content += '<img src="';
                            content += element.SendUserImage;
                            content += '">';
                        }


                        content += '<font class="user-id">';
                        content += element.iSendUserName;
                        content += '</font>';

                        content += '<font class="bell-content-text">';
                        content += element.content;
                        content += '</font> </div>';
                    }

                    r.allpush.forEach(Type);
                    $('#' + bell_page_id + ' .bell-list').html(content);

                    Global.change_pg(now_page, bell_page_id);
                    Global.set_last_page(now_page);
                    Global.set_now_page(bell_page_id);
                    console.log('now:' + now_page);
                    console.log('last:' + last_page);
                } else {
                    alert(r.message);
                }
            });
        }
    }
    //login-page-content
/////login-page page event
var Login_page_content = {
        Login: function () {
            var account = $('#account-input').val();
            var password = $('#password-input').val();

            if (account == '' || password == '') {
                alert('所有欄位皆為必填');
                return 0;
            }

            var toServer = {
                account: account,
                password: password
            }

            Ajax.Post('Login', toServer, function (r) {
                if (r.status == 1) {
                    Forever.setItem('ac', r.info);
                    Forever.setItem('uId', r.uId);

                    Local.set('ac', r.info);
                    Local.set('uId', r.uId);
                    Local.set('account', account);
                    Local.set('password', password);
                    var content = '';
                    var uId = Local.get('uId') || Forever.getItem('uId');
                    $('#' + newsfeed_page_id + ' #forum-name').html(r.forumname);

                    function Type(element, index, array) {
                        content += '<div class="single-newsfeed-content"> <div class="channel-profile-content" id="go-to-channel">';

                        if (element.userImg !== '') {
                            content += '<img src="';
                            content += element.userImg;
                            content += '">';
                        } else {
                            content += '<img src="demo/grey-background-square.jpg">';
                        }

                        content += '<font class="channel-name">';
                        content += element.iUserId;
                        content += '</font>';

                        if (element.postUserId == uId) {
                            content += '<span class="post-by-self" onclick="newsfeed_dropdown(';
                            content += element.iId;
                            content += ')"><img src="demo/drop-arrow.png"></span>';
                            content += '<div id="drop';
                            content += element.iId;
                            content += '" class="dropdown-content">';
                            content += '<button onclick="newsfeed_dropdown_edit(';
                            content += element.iId;
                            content += ')">編輯</button>';
                            content += '<button onclick="newsfeed_dropdown_delete(';
                            content += element.iId;
                            content += ')">刪除</button>';
                            content += '<button class="cancel" onclick="newsfeed_dropdown_cancel(';
                            content += element.iId;
                            content += ')">取消</button>';
                            content += '</div>';
                        } else {
                            console.log('back return: ' + element.postUserId);
                            console.log('local uid: ' + uId);
                        }

                        content += '<br><font class="create-time">';
                        content += element.iCreateTime;
                        content += '</font> </div>';
                        if (element.vImage === '') {
                            //                   content += '<img src="demo/post_demo.png" class="post-img">';
                        } else {
                            content += '<img src="';
                            content += element.vImage
                            content += '" class="post-img">';
                        }

                        if (element.vVideo === '') {

                        } else {
                            content += '<video controls class="post-video" src="';
                            content += element.vVideo;
                            content += '"></video>';
                        }


                        var substr = Global.countSubstr(element.vPostContent, '<br>', true);
                        if (element.vPostContent.length > 30) {
                            var subcontent = element.vPostContent.substr(0, 19);
                            content += ' <div class="text-content"> <font>';
                            content += subcontent;
                            content += '</font>';
                            content += '<button class="keep-reading">繼續閱讀</button> ';

                        } else if (substr > 4) {
                            //var subcontent = '';
                            content += ' <div class="text-content"> <font>';
                            content += element.vPostContent;
                            content += '</font>';
                            content += '<button class="keep-reading">繼續閱讀</button> ';
                        } else {
                            content += ' <div class="text-content"> <font>';
                            content += element.vPostContent;
                            content += '</font>';
                        }

                        content += '<hr> <div class="client-button"> <button onclick="PostLove(this.id,';
                        content += element.iId;
                        content += ')" id="';
                        content += 'postlove' + element.iId;
                        content += '"';
                        if (element.clicklove == 1) {
                            content += 'click="1"';
                            content += '><img src="demo/heart-icon.png">';
                        } else {
                            content += '><img src="demo/heart-grey-icon.png">';
                        }

                        content += element.bLove;
                        content += '</button>';
                        content += '<button onclick="NewsfeedComment(';
                        content += element.iId;
                        content += ')"><img src="demo/comment-icon.png">';
                        content += element.bComment;
                        content += '</button> ';
                        content += '<button onclick="NewsFeedStorage()"><img src="demo/storage.png">';
                        content += element.bStorage;
                        content += '</button> </div></div></div>';
                    }

                    r.post.forEach(Type);

                    $('#' + newsfeed_page_id + ' .all-newsfeed-content').html(content);
                    Global.change_bg('white');
                    Global.change_pg(login_page_id, newsfeed_page_id);
                    $('#newsfeed-btn-hottest').css('color', '#FF3399');
                    $('#newsfeed-btn-hottest').css('border-bottom', '4px solid #FF3399');
                    $('#newsfeed-btn-newest').css('border-bottom', '4px solid white');
                    $('#bottom-bar').show();
                    Global.BottomBell();
                    Global.set_now_page(newsfeed_page_id);


                    console.log('now:' + now_page);
                    console.log('last:' + last_page);
                } else {
                    alert(r.message);
                }
            });
        },
        GoRegisterPage: function () {
            Global.change_pg(login_page_id, register_page_id);
        }
    }
    //register-page-content
/////Register_page page event
var Register_page_content = {
        Register: function () {
            var password = $('#password-register').val();
            var account = $('#account-register').val();
            var phone = $('#phone-register').val();
            if (password == '' || account == '' || phone == '') {
                alert('所有欄位皆為必填');
                return 0;
            }
            var toServer = {
                phonenum: phone,
                account: account,
                password: password
            }

            Ajax.Post('Register', toServer, function (r) {
                if (r.status == 1) {
                    Global.change_pg(register_page_id, login_page_id);
                } else {
                    alert(r.message);
                }
            });
            console.log('now:' + now_page);
            console.log('last:' + last_page);
        },
        GoLoginPage: function () {
            Global.change_pg(register_page_id, login_page_id);
        }
    }
    //newsfeed-page-content
/////newsfeed-page page event 結果好像沒用到
var Newsfeed_page_content = {}
/////post-page page event
var Post_page_content = {
        PostChooseForum: function () {
            var content = '';
            Ajax.Get('PostChooseForum', function (r) {
                if (r.status === 1) {

                    function Type(element, index, array) {
                        content += '<button onclick="PostToForum(this.id,this.name)" name="';
                        content += element.vName + '"id="';
                        content += element.iId;
                        content += '"><img src="demo/TKU-logo.jpg"><font>';
                        content += element.vName;
                        content += '</font></button>';
                    }

                    r.info.forEach(Type);
                    $('#' + post_page_id + ' .dropdown-content').html(content);
                } else {
                    alert(r.message);
                }
            });
            $('#' + post_page_id + ' .dropdown-content').show();
        },
        PostNewsFeed: function () {
            var forumtype = $('#' + post_page_id + ' .post-button').attr('item');
            var text = $('#' + post_page_id + ' .text-content textarea').val().replace(/\n|\r\n/g, "<br>");
            var img = $('#' + post_page_id + ' .text-content img').attr('src');
            var video = $('#' + post_page_id + ' #post-onchange-video').attr('src');

            var toServer = {
                ac: Forever.getItem('ac'),
                forum: forumtype,
                content: text,
                img: img,
                video: video
            }

            Ajax.Post('PostNewsfeed', toServer, function (r) {
                if (r.status === 1) {
                    var forumtype = $('#' + post_page_id + ' .post-button').attr('item', '0');
                    var text = $('#' + post_page_id + ' .text-content textarea').text('');
                    var img = $('#' + post_page_id + ' .text-content img').attr('src', '');

                    var item = ForumSession;
                    oftenCaller.GoNewsFeed();

                } else {
                    alert(r.message);
                }
            });
        }
    }
    //newsfeed-comment-page-content
/////newfeed-comment page event
var Newsfeed_comment_page_content = {
    CommentInput: function (id) {
        var input = $('#' + newsfeed_comment_id + ' #comment_input').val();
        var toServer = {
            ac: Forever.getItem('ac'),
            comment: input,
            postid: id
        }

        Ajax.Post('Comment', toServer, function (r) {
            if (r.status === 1) {
                $('#' + newsfeed_comment_id + ' #comment_input').val('');
                $('#' + newsfeed_comment_id + ' .button-to-comment').attr('id', '');

                Ajax.Post('GetComment', toServer, function (r) {
                    if (r.status === 1) {
                        var content = '';

                        function Type(element, index, array) {
                            content += '<div class="single-comment-content"> <div class="single-comment-top">';

                            if (element.UserImage == '') {
                                content += '<img src="demo/reading.png" class="comment-profile">';
                            } else {
                                content += '<img src="';
                                content += element.UserImage;
                                content += '" class="comment-profile">';
                            }
                            content += '<font class="comment-UserId">';
                            content += element.iUserId;
                            content += '</font>';

                            content += '<button><img src="demo/heart-icon.png">';
                            content += element.bLove;
                            content += '</button> <br><font class="comment-create-time">';
                            content += element.iCreateTime;
                            content += '</font> </div><div class="single-comment-bottom"> <font>';
                            content += element.vCommentContent;
                            content += '</font> </div></div>';
                        }

                        r.info.forEach(Type);
                        $('#' + newsfeed_comment_id + ' .all-single-newsfeed-comments').html(content);

                    } else {
                        alert(r.message);
                    }
                })

            } else {
                alert(r.message);
            }
        })
    }
}
/////設定頁面的 自定義function
var SettingPage = {
    MySchedule: function () {
        Global.change_pg(now_page, class_schedule_page_id);
        Global.set_last_page(now_page);
        Global.set_now_page(class_schedule_page_id);
        console.log('now: ' + now_page);
        console.log('last: ' + last_page);

        var toServer = {
            ac: Forever.getItem('ac') || Local.get('ac')
        }
        Ajax.Post('getClassSchedule', toServer, function (r) {
            if (r.status === 1) {
                var content = '';

                function Type(element, index, array) {
                    content += '<div class="single-class"><div class="top"><font class="className">';
                    content += element.vClassname;
                    content += '</font><font class="score">';
                    content += element.vClassScore;
                    content += '學分';
                    content += '</font></div><div class="bottom"><font class="classroom">';
                    content += element.vClassRoom;
                    content += '</font><font class="classtime">';
                    content += element.iClassTime;
                    content += '</font></div></div>';
                }

                r.info.forEach(Type);
                $('#' + class_schedule_page_id + ' .content').html(content);
            } else {
                alert(r.message);
            }
        })
    },
    LogOut: function () {
        Global.change_bg(main_color);
        Global.change_pg(now_page, login_page_id);
        Global.set_last_page('');
        Global.set_now_page('');

        Forever.removeItem('ac');
        Forever.removeItem('uId');

        Local.remove('ac');
        Local.remove('account');
        Local.remove('password');
        Local.remove('uId');

    },
    MyTestSchedule: function () {
        var toServer = {
            ac: Forever.getItem('ac') || Local.get('ac')
        }

        Ajax.Post('getTestSchedule', toServer, function (r) {
            if (r.status === 1) {
                Global.change_pg(now_page, class_schedule_page_id);
                Global.set_last_page(now_page);
                Global.set_now_page(class_schedule_page_id);
                console.log('now: ' + now_page);
                console.log('last: ' + last_page);
                var content = '';

                function Type(element, index, array) {
                    content += '<div class="single-class"><div class="top"><font class="className">';
                    content += element.iClassId;
                    content += '</font><font class="score">';
                    content += '</font></div><div class="bottom"><font class="classroom">';
                    content += element.vTestRoom;
                    content += '</font><font class="classtime">';
                    content += '考試時間: ';
                    content += element.vTestTime;
                    content += '</font></div></div>';
                }

                r.info.forEach(Type);
                $('#' + class_schedule_page_id + ' .content').html(content);

            } else {
                console.log(r);
            }
        });
    },
    MyTestScore: function () {
        var toServer = {
            ac: Forever.getItem('ac') || Local.get('ac')
        }

        Ajax.Post('getTestScore', toServer, function (r) {
            if (r.status === 1) {
                Global.change_pg(now_page, class_schedule_page_id);
                Global.set_last_page(now_page);
                Global.set_now_page(class_schedule_page_id);
                console.log('now: ' + now_page);
                console.log('last: ' + last_page);
                var content = '';

                function Type(element, index, array) {
                    content += '<div class="single-class"><div class="top"><font class="className">';
                    content += element.iClassId;
                    content += '</font><font class="score">';
                    content += '</font></div><div class="bottom">';
                    content += '<font class="classtime">';
                    content += '期末成績: ';
                    content += element.vScore;
                    content += '</font></div></div>';
                }

                r.info.forEach(Type);
                $('#' + class_schedule_page_id + ' .content').html(content);
            } else {
                console.log(r);
            }
        });
    },
    TkuWifiConnect: function () {
        alert('還沒寫 87 別亂玩T');
    }
}
///////////整合過的各頁面event listener



//未完成 css->border-bottom newsfeed-page 的 最新貼文
function newsfeed_newest() {
    item = ForumSession;
    oftenCaller.GoNewsFeed();
}
//未完成 css->border-bottom newsfeed-page 的 最熱門貼文
function newsfeed_hottest() {
    item = ForumSession;
    oftenCaller.GoNewsFeed(2);
}

/////勝為說要用的post video domo function
function demoVideo(e) {
    var file = this.files[0];
    var reader = new FileReader();
    reader.onload = viewer.load;
    reader.readAsDataURL(file);
    //viewer.setProperties(file);

    var viewer = {
        load: function (e) {
            $('#post-page-content #post-onchange-video').attr('src', e.target.result);
        }
    }
}

/////click to friends-list-page
function GoFriendlist() {
    var param = {
        ac: Forever.getItem('ac')
    }

    Ajax.Post('getFriendList', param, function (r) {
        if (r.status === 1) {
            var content = '';

            function Type(element, index, array) {
                content += '<div class="single-letter" onclick="GoLetterPage(';
                content += element.iId;
                content += ')"> <div class="img"> ';

                if (element.vImage === '') {
                    content += '<img src="demo/grey-background-square.jpg">';
                } else {
                    content += '<img src="';
                    content += element.vImage;
                    content += '">';
                }

                content += '</div><div class="singlecontent"> <div class="name">';
                content += element.vName;

                if (element.getRoom === '') {
                    content += '</div><div class="message"> 尚未有對話紀錄... </div></div></div>';

                } else {

                    content += '</div><div class="message">';
                    content += element.lasttalk;
                    content += '</div></div></div>';

                }
            }

            r.info.forEach(Type);
            $('#' + friend_list_page_id + ' .content').html(content);


            Global.change_pg(now_page, friend_list_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(friend_list_page_id);

            console.log('last-page:' + last_page);
            console.log('now-page:' + now_page);
        } else {
            alert(r.message);
        }
    })

}

/////按貼文愛心讚的function 搞好久
function PostLove(id, pid) {
    var click = $('#' + newsfeed_page_id + ' #' + id).attr('click');
    if (click == 1) {
        var toServer = {
            ac: Forever.getItem('ac'),
            postid: pid,
            love: 0
        };
        Ajax.Post('PostLove', toServer, function (r) {
            if (r.status === 1) {
                $('#' + newsfeed_page_id + ' #' + id).attr('click', '');
                var content = '';
                content += '<img src="demo/heart-grey-icon.png">';
                content += r.info.bLove;
                $('#' + newsfeed_page_id + ' #' + id).html(content);
            } else {
                alert(r.message);
            }
        })
    } else {
        var toServer = {
            ac: Forever.getItem('ac'),
            postid: pid,
            love: 1
        };

        Ajax.Post('PostLove', toServer, function (r) {
            if (r.status === 1) {
                $('#' + newsfeed_page_id + ' #' + id).attr('click', '1');
                var content = '';
                content += '<img src="demo/heart-icon.png">';
                content += r.info.bLove;
                $('#' + newsfeed_page_id + ' #' + id).html(content);
            } else {
                alert(r.message);
            }
        })
    }
}

/////和朋友對話的page 的 return button
function letterpagereturn() {
    console.log(last_page);
    Global.change_pg(now_page, friend_list_page_id);
    Global.set_now_page(friend_list_page_id);
    Global.set_last_page(letter_page_id);
    Global.ShowBottom();
}

/////朋友列表的return button
function friendlistreturn() {
    Global.change_pg(now_page, newsfeed_page_id);
    Global.set_last_page(newsfeed_page_id);
    Global.set_now_page(newsfeed_page_id);

}

/////other page change username 最後發現不行 一定要加這個功能上去
function OtherChangeUserName() {
    var content = '<input type="text" style="border: 1px solid grey" placeholder="輸入新名稱..." id="other_input_newName">';
    content += '<button onclick="confirm_edit_name()">確認</button>';
    $('#' + other_page_id + ' .profile-name').html(content);
}

/////跳出input value後 按下確認會call api 更改DB的資料欄位
function confirm_edit_name() {
    var change = $('#' + other_page_id + ' #other_input_newName').val();

    var toServer = {
        ac: Local.get('ac') || Forever.get('ac'),
        change: change
    }

    Ajax.Post('changeUserName', toServer, function (r) {
        var toServer = {
            ac: Forever.getItem('ac')
        };
        Ajax.Post('otherpage', toServer, function (r) {
            if (r.status === 1) {
                if (r.info.vImage != '') {
                    $('#other-page-content #other-page-profile-img').attr('src', r.info.vImage);
                }
                var content = '<button onclick="OtherChangeUserName()"> <font id="other-page-profile-name">';
                content += r.info.vName;
                content += '</font> <img src="demo/edit2.png"> </button>';

                $('#' + other_page_id + ' .profile-name').html(content);
            } else {
                alert(r.message);
            }
        });
        Global.change_pg(now_page, other_page_id);
        Global.set_last_page(now_page);
        Global.set_now_page(other_page_id);
        console.log('now:' + now_page);
        console.log('last:' + last_page);
    });
}

/////對話page的 確認 第一次聊天開啟
function TalkSubmit(id) {
    var content = $('#' + letter_page_id + ' .input-content #wannatalk').val();
    var toServer = {
        ac: Forever.getItem('ac'),
        sentto: id,
        content: content
    }

    Ajax.Post('createchat', toServer, function (r) {
        if (r.status === 1) {
            var content = $('#' + letter_page_id + ' .input-content #wannatalk').val('');
            GoLetterPage(id);
        } else {}
    })
}

/////click to letter_page_id
function GoLetterPage(id) {
    $('#' + letter_page_id + ' .input-content button').attr('id', id);
    var toServer = {
        ac: Forever.getItem('ac'),
        sentto: id
    }

    Ajax.Post('getchatroom', toServer, function (r) {
        if (r.status === 1) {
            var content = '';

            function Type(element, index, array) {
                if (element.sentby == 1) {
                    content += '<div class="my-talk"> <div class="profile">';
                    if (element.iSentUserImage != '') {
                        content += '<img src="';
                        content += element.iSentUserImage;
                        content += '"> <font>';
                    } else {
                        content += '<img src="demo/grey-background-square.jpg"> <font>';
                    }
                    content += element.iSentUser;
                    content += '</font><font class="time">';

                    content += element.iCreateTime;
                    content += '</font></div><div class="content"><font>';
                    content += element.vContent;
                    content += '</font></div></div>';
                } else {
                    content += '<div class="other-talk"> <div class="profile">';

                    if (element.iSentUserImage != '') {
                        content += '<img src="';
                        content += element.iSentUserImage;
                        content += '"> <font>';
                    } else {
                        content += '<img src="demo/grey-background-square.jpg"> <font>';
                    }
                    content += element.iSentUser;
                    content += '</font><font class="time">';
                    content += element.iCreateTime;
                    content += '</font></div><div class="content"><font>';
                    content += element.vContent;
                    content += '</font></div></div>';
                }

            }

            r.info.forEach(Type);
            $('#' + letter_page_id + ' .talk-content').html(content);
            Global.change_pg(now_page, letter_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(letter_page_id);
            Global.HideBottom();
            console.log('nowpage:' + now_page);
            console.log('lastpage:' + last_page);
        } else if (r.status === 2) {
            Global.change_pg(now_page, letter_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(letter_page_id);
            Global.HideBottom();
            console.log('nowpage:' + now_page);
            console.log('lastpage:' + last_page);
        } else {
            var content = '<div class="No-Content">尚未有對話紀錄...</div>';
            $('#' + letter_page_id + ' .nav-bar').after(content);
        }
    })


}

/////有點忘了 等等註解
function PostToForum(id, name) {
    $('#' + post_page_id + ' .post-button').attr('item', id);
    $('#' + post_page_id + ' .dropdown-content').hide();
    $('#' + post_page_id + ' .channel-name font').html(name);
}

/////從newsfeedpage click to forum page
function NewsFeedGoForumType() {
    Ajax.Get('GetForumType', function (r) {
        if (r.status === 1) {
            var content = '';
            var content_meta = '';

            function Type(element, index, array) {
                content += '<div class="group"> <div class="bigType"> <font class="name">';
                content += element.vName;
                content += '</font> </div>';

                function Type_Meta(element, index, array) {
                    content_meta += "<div class='single-forum' id='" + element.iId + "' onclick='ChooseForum(this.id)'> <div class='img'> <img src='demo/TKU-logo.jpg'> </div><div class='name'>" + element.vName + "</div></div>";
                }
                element.detail.forEach(Type_Meta);
                content += content_meta;
                content_meta = '';

                content += '<div>';
            }

            r.info.forEach(Type);
            $('#forum-type-page-content .content').html(content);
            Global.change_pg(now_page, forum_type_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(forum_type_page_id);
        } else {
            console.log('fail');
        }
    });

}

////forun page choose forun
function ChooseForum(item) {

    ForumSession = item;
    oftenCaller.GoNewsFeed();
}

//////未完成  newsfeed 的收藏貼文
function NewsFeedStorage() {
    alert('還沒寫,87別亂玩');
}

//////點擊bottom-bar的 好友先確認今天有沒有抽過
function LookFriendShip(type, relateId) {
    //type == push的type
    //relateid == 關聯Id
    var toServer = {
        type: type,
        relateId: relateId,
        ac: Forever.getItem('ac')
    }
    Ajax.Post('LookWhoPullMe', toServer, function (r) {
        if (r.status === 1) {
            var content = '';
            content += '<div class="wallpaper"> ';
            content += '<img src="demo/grey-wallpaper-15.jpg">';
            content += '</div><div class="profile"> ';
            if (r.info.vImage === 0) {
                content += '<img src="demo/grey-background-square.jpg">';
            } else {
                content += '<img src="';
                content += r.info.vImage;
                content += '">';
            }

            content += '</div><div class="name"> <p>';
            content += r.info.vName;
            content += '</p></div>';
            content += '<div class="review"><p>自我介紹區自我介紹區自我介紹區自我介紹區自我介紹區自我介紹區</p></div>';
            content += '<div class="sendinvite"> <button onclick="RecieveInvite(' + r.info.iId + ')"> 確認對方好友邀請 </button> </div>';
            $('#card-friends-page-content .change').html(content);
            Global.change_pg(now_page, card_friends_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(card_friends_page_id);
        } else {
            alert(r.message);
        }

    });
}

//////忘了 幹
function RecieveInvite(id) {
    //此id為要接受的使用者的id
    var toServer = {
        ac: Forever.getItem('ac'),
        sentuser: id
    }

    Ajax.Post('AcceptInvite', toServer, function (r) {
        if (r.status === 1) {

            var param = {
                ac: Forever.getItem('ac')
            }

            Ajax.Post('getFriendList', param, function (r) {
                if (r.status === 1) {
                    var content = '';

                    function Type(element, index, array) {
                        content += '<div class="single-letter" onclick="GoLetterPage(';
                        content += element.iId;
                        content += ')"> <div class="img"> ';

                        if (element.vImage === '') {
                            content += '<img src="demo/grey-background-square.jpg">';
                        } else {
                            content += '<img src="';
                            content += element.vImage;
                            content += '">';
                        }

                        content += '</div><div class="singlecontent"> <div class="name">';
                        content += element.vName;
                        content += '</div><div class="message"> 放最後一個對話的地方.... </div></div></div>';
                    }

                    r.info.forEach(Type);
                    $('#' + friend_list_page_id + ' .content').html(content);


                    Global.change_pg(now_page, friend_list_page_id);
                    Global.set_last_page(now_page);
                    Global.set_now_page(friend_list_page_id);
                    console.log('nowpage:' + now_page);
                    console.log('lastpage:' + last_page);
                } else {
                    alert(r.message);
                }
            })
        } else {
            alert(r.message);
        }
    });
}

//////baby tube 專案用的
function CreateChannel() {
    Global.change_pg(now_page, create_channel_page_id);
    Global.set_last_page(now_page);
    Global.set_now_page(create_channel_page_id);
    console.log('now:' + now_page);
    console.log('last:' + last_page);
}

/////post page 的 return button
function PostReturn() {
    Global.change_pg(post_page_id, last_page);
    $('#bottom-bar').show();
    Global.set_now_page(last_page);
    Global.set_last_page(post_page_id);
    console.log('now:' + now_page);
    console.log('last:' + last_page);
}

/////忘了 function 名曲跟另一個好像
function NewsfeedComment(id) {
    var toServer = {
        ac: Forever.getItem('ac'),
        postid: id
    }

    console.log(toServer);

    Ajax.Post('GetComment', toServer, function (r) {
        if (r.status === 1) {
            console.log(r);
            var content = '';

            function Type(element, index, array) {
                content += '<div class="single-comment-content"> <div class="single-comment-top">';

                if (element.UserImage == '') {
                    content += '<img src="demo/reading.png" class="comment-profile">';
                } else {
                    content += '<img src="';
                    content += element.UserImage;
                    content += '" class="comment-profile">';
                }
                content += '<font class="comment-UserId">';
                content += element.iUserId;
                content += '</font>';

                content += '<button><img src="demo/heart-icon.png">';
                content += element.bLove;
                content += '</button> <br><font class="comment-create-time">';
                content += element.iCreateTime;
                content += '</font> </div><div class="single-comment-bottom"> <font>';
                content += element.vCommentContent;
                content += '</font> </div></div>';
            }

            r.info.forEach(Type);
            $('#' + newsfeed_comment_id + ' .all-single-newsfeed-comments').html(content);

        } else {
            alert(r.message);
        }
    })
    console.log(id);
    $('#bottom-bar').hide();
    $('#' + newsfeed_page_id).slideUp();
    $('#' + newsfeed_comment_id).show();
    $('#' + newsfeed_comment_id + ' .button-to-comment').attr('id', id);
}

/////點擊更換大頭貼出現的preview的 cancel button
function PreivewImgShowReturn() {
    Global.change_pg(now_page, last_page);
    Global.set_now_page(last_page);
    console.log('now:' + now_page);
    console.log('last:' + last_page);
    var src = $('#img-upload-preview-show').attr('src', '');
    $('#bottom-bar').show();
}

/////已刪 另一個project裡面有
function CreateChannelReturn() {
    Global.change_pg(now_page, last_page);
    Global.set_now_page(last_page);
    console.log('now:' + now_page);
    console.log('last:' + last_page);
}

/////other page go setting
function GoSetting() {
    Global.change_pg(now_page, setting_page_id);
    Global.set_last_page(now_page);
    Global.set_now_page(setting_page_id);
}

/////call api for 抽卡
function PullCard(item) {
    var toServer = {
        ac: Forever.getItem('ac'),
        sendid: item
    }
    console.log(toServer);
    Ajax.Post('SendInvite', toServer, function (r) {
        if (r.status === 1) {
            console.log(r.message);

            var content = '';

            content += '<p>你已送出邀請<p/>';

            $('#' + card_friends_page_id + ' .sendinvite').html(content);
        } else {
            alert(r.message);
        }
    });
    Global.change_pg(now_page, card_friends_page_id);
    Global.set_last_page(now_page);
    Global.set_now_page(card_friends_page_id);
}

/////忘了 怎麼感覺好像跟pull card一樣
function getCard() {
    var toServer = {
        ac: Forever.getItem('ac')
    }
    Ajax.Post('PullCard', toServer, function (r) {
        if (r.status === 1) {
            console.log(r);
            var content = '';
            content += '<div class="wallpaper"> ';
            content += '<img src="demo/grey-wallpaper-15.jpg">';
            content += '</div><div class="profile"> ';
            if (r.info.vImage === 0) {
                content += '<img src="demo/grey-background-square.jpg">';
            } else {
                content += '<img src="';
                content += r.info.vImage;
                content += '">';
            }

            content += '</div><div class="name"> <p>';
            content += r.info.vName;
            content += '</p></div>';
            content += '<div class="review"><p>自我介紹區自我介紹區自我介紹區自我介紹區自我介紹區自我介紹區</p></div>';
            content += '<div class="sendinvite"> <button onclick="PullCard(' + r.info.iId + ')"> 向對方送出邀請 </button> </div>';
            $('#card-friends-page-content .change').html(content);
            Global.change_pg(now_page, card_friends_page_id);
            Global.set_last_page(now_page);
            Global.set_now_page(card_friends_page_id);
        } else {
            alert(r.message);
        }
    });

}





/////////////以下都不知道要衝三小的////////////////
///先放著 之後再說
/////newsfeed page event listener
$('#go-to-channel').click(function () {
    change_pg(now_page, channel_profile_page_id);
    set_last_pg(now_page);
    set_now_page(channel_profile_page_id);
})

//////channel profile page event listener
$('#channel-return-last-page').click(function () {
    change_pg(now_page, last_page);
    set_now_page(last_page);
    set_last_pg(channel_profile_page_id);
})

$('#channel-profile-newsfeed-img').click(function () {
    change_pg(now_page, single_newsfeed_id);
    set_last_pg(now_page);
    set_now_page(single_newsfeed_id);
})

//////single newsfeed page event listener
$('#single-newsfeed-return-last-page').click(function () {
    change_pg(now_page, last_page);
    set_now_page(last_page);
    set_last_pg(single_newsfeed_id);
})

$('#single-newsfeed-go-to-channel').click(function () {
    change_pg(now_page, channel_profile_page_id);
    set_last_pg(now_page);
    set_now_page(channel_profile_page_id);
})

//////newsfeed comment page event listener
$('#newsfeed-comment-return-button').click(function () {
    $('#newsfeed-page-content').slideDown();
    $('#bottom-bar').show();
    $('#newsfeed-comment-page-content').hide();
    //$('#newsfeed-page-content').show();
})
