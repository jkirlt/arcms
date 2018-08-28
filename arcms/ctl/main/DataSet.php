<?php
/**
 * 前端基于layuicms2.0 ，后端基于arphp 5.1.14
 *
 * @author assnr assnr@coopcoder.com
 *
 * 本项目仅供学习交流使用，如果用于商业请联系授权
 */
namespace arcms\ctl\main;

/**
 * 内容设置页面控制器
 * 该控制器接受权限访问限制
 */
class DataSet extends Base
{
    // 企业信息
    public function about()
    {
        $this->display('/dataSet/about');
    }

    // 文章管理
    public function article()
    {
        $this->display('/dataSet/article');
    }

    // 产品管理
    public function product()
    {
        $this->display('/dataSet/product');
    }

    // 新闻管理
    public function news()
    {
        $this->display('/dataSet/news');
    }

    // 图片管理
    public function image()
    {
        $this->display('/dataSet/image');
    }

    // 下载管理
    public function downloadFile()
    {
        $this->display('/dataSet/downloadFile');
    }

    // 招聘管理
    public function jobs()
    {
        $this->display('/dataSet/jobs');
    }

    // 消息管理
    public function message()
    {
        $this->display('/dataSet/message');
    }
}
