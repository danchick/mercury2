<?php
    class _blog extends blog {
//    class _blog {
        // build object here
        function postdetail(){
            echo "oo site";
//            $post = new blog_post($m->getInVariable('BlogPostId'));
        }



        function postdetail3(){
            echo "oo site 3";
            m("blog", "printRed", '');
        }



    }
?>