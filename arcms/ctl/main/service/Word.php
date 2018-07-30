<?php
/**
 * @Author: Marte
 * @Date:   2018-07-18 10:07:19
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-07-27 14:41:27
 */

namespace arcms\ctl\main\service;
class Word
{
    public function downAsWord($companyid, $uid, $num)
    {
        define("ARCMS_RATH_WORD", dirname(dirname(dirname(dirname(__FILE__)))));
        // var_dump(ARCMS_RATH_WORD);exit('ARCMS_RATH_WORD');
         // 查询评估报告编号等信息，用以写入评估报告
        $summaryReport = \arcms\lib\model\ValueSummaryReport::model()->getDb()
            ->where(['num' => $num, 'status' => 2])//展示中
            ->queryRow();

        // 企业基本信息
        $companyInfo = \arcms\lib\model\ValueCompanyInformation::model()->getDb()
            ->where(['report_num' => $num, 'isevaluate' => 1])
            ->queryRow();

        // 所属行业
        $hangye = \arcms\lib\model\ValueIndustryIndicator::model()->getDb()
            ->where(['id' => $companyInfo['industry']])
            ->queryRow();
        $companyInfo['hangye'] = $hangye['second_industry'];

        // 所处阶段
        $jieduan = \arcms\lib\model\ValueCompanyStage::model()->getDb()
            ->where(['id' => $companyInfo['stage_id']])
            ->queryRow();
        $companyInfo['jieduan'] = $jieduan['stage'];

        // 评估记录
        $evaluationRecords = \arcms\lib\model\ValueEvaluationRecords::model()->getDb()
            ->where(['report_num' => $num,'isend' => 1])
            ->queryRow();

        // 创新能力第2题，企业的科研人员占比
        switch ($evaluationRecords["cx2"]) {
            case "a":
                $companyInfo["kyryzb"] = "0（含）-10%";
                break;
            case "b":
                $companyInfo["kyryzb"] = "10%（含）-25%";
                break;
            case "c":
                $companyInfo["kyryzb"] = "25%（含）-40%";
                break;
            case "d":
                $companyInfo["kyryzb"] = "40%（含）-60%";
                break;
            case "e":
                $companyInfo["kyryzb"] = "60%（含）以上";
                break;
            
            default:
                # code...
                break;
        }

        // 知识产权  soft
        $companyInfo["zscq"] = $companyInfo["patent"] + $companyInfo["shiyong"] + $companyInfo["design"] + $companyInfo["soft"];

        // 创新能力第8题，产品级别
        switch ($evaluationRecords["cx8"]) {
            case "a":
                $companyInfo["cpjb"] = "行业级";
                break;
            case "b":
                $companyInfo["cpjb"] = "市级";
                break;
            case "c":
                $companyInfo["cpjb"] = "省级";
                break;
            case "d":
                $companyInfo["cpjb"] = "国家级";
                break;
            case "e":
                $companyInfo["cpjb"] = "无";
                break;
            
            default:
                # code...
                break;
        }

        // 创新能力第9题，产品是否被列入
        switch ($evaluationRecords["cx9"]) {
            case "a":
                $companyInfo["cpsfblr"] = "国家863计划";
                break;
            case "b":
                $companyInfo["cpsfblr"] = "国家火炬计划";
                break;
            case "c":
                $companyInfo["cpsfblr"] = "国家重点新产品";
                break;
            case "d":
                $companyInfo["cpsfblr"] = "无";
                break;
            default:
                # code...
                break;
        }

        // 创新能力第10题，正在研发的产品或技术
        switch ($evaluationRecords["cx10"]) {
            case "a":
                $companyInfo["zzyf"] = "是";
                break;
            case "b":
                $companyInfo["zzyf"] = "否";
                break;
            default:
                # code...
                break;
        }

        // 财务状况 
        $lastyear = date('Y', time()) - 1;  // 最近一年的年份
        $financialDatas = \arcms\lib\model\ValueFinancialData::model()->getDb()
            ->where(['report_num' => $num])
            ->queryAll();
        foreach ($financialDatas as &$financialData) {
            foreach ($financialData as $financialData_key => &$financialData_value) {
                $financialData_value = ($financialData_value > 0) ? $financialData_value:"-";
                
            }
            
        }

        // 财务预测 
        $financialForecast = \arcms\lib\model\ValueFinancialForecast::model()->getDb()
            ->where(['report_num' => $num])
            ->queryRow();

        // 企业评估得分
        $companyScore = \arcms\lib\model\ValueCompanyScore::model()->getDb()
            ->where(['report_num' => $num])
            ->queryRow();
        $companyScore['shizhi'] = round($companyScore['value']*10000*10000); // 元


        // 企业评估得分的星级和评价
        $starReview = \arcms\lib\model\ValueStarReview::model()->getDb()
            ->where(['id' => $companyScore['review_id']])
            ->queryRow();

        // 因子得分
        $factorScore = \arcms\lib\model\ValueFactorScore::model()->getDb()
            ->where(['report_num' => $num])
            ->queryRow();

        // 指标层和因子得分率
        $factorScoreRate = \arcms\lib\model\ValueFactorScoreRate::model()->getDb()
            ->where(['report_num' => $num])
            ->queryRow();

        // 筛选43个因子中得分率最低的7个因子
        // 筛选出43个因子
        for ($i=1; $i <= 43; $i++) { 
            $tmp1 = 'rate'.$i;
            $yinziRate[$tmp1] = $factorScoreRate[$tmp1];
        }

        // 以下9项因子无法写评语，所以筛选得分率最低的因子时，排除这几项指标
        // rate12:创始人公司关注度,
        // rate18:主营业务收入增长率,
        // rate19:净利润增长率,
        // rate20:净资产增长率,
        // rate30:行业阶段,
        // rate39:流动比率,
        // rate40:速动比率,
        // rate41:总资产净利率,
        // rate43:主营业务净利率
        unset($yinziRate['rate12'], $yinziRate['rate18'], $yinziRate['rate19'], $yinziRate['rate20'], $yinziRate['rate30'], $yinziRate['rate39'], $yinziRate['rate40'], $yinziRate['rate41'], $yinziRate['rate43']);

        switch ($companyInfo['stage_id']) {  
            case '1':
                // 企业处于初创期时，没有成长能力的4个因子，所以这4项不参与筛选
                // rate18：主营业务收入增长率
                // rate19：净利润增长率
                // rate20：净资产增长率
                // rate21：总资产增长率
                unset($yinziRate['rate21'] );
                break;
            default:
                break;
        }

        // 筛选出15个指标
        for ($j=1; $j <= 15; $j++) { 
            $tmp2 = 'tarrate'.$j;
            $zhibiaoRate[$tmp2] = $factorScoreRate[$tmp2];
        }

        asort($yinziRate);  // 按键对关联数组进行升序排序

        // 从分值最低的得分率，找出对应的因子
        foreach ($yinziRate as $yinziRate_key => &$yinziRate_value) {
            $asortYinziRate[] = $yinziRate_key;
        }

        // 从得分率最低的因子，找出对应的评语，已经升序排序
        foreach ($asortYinziRate as $asortYinziRate_key => &$asortYinziRate_value) {
            $lowYinzi[] = 'factor'.substr($asortYinziRate_value, 4);// 截取rate后的字符
        }

        // 15个指标得分率  $zhibiaoRate
        asort($zhibiaoRate);  // 升序排序

        // 从15个指标中，找出得分率低的指标，已经升序排序
        foreach ($zhibiaoRate as $zhibiaoRate_key => &$zhibiaoRate_value) {
            $asortZhibiaoRate[] = $zhibiaoRate_key;
        }

        // 从指标里面，找出指标的名称
        foreach ($asortZhibiaoRate as $asortZhibiaoRate_key => &$asortZhibiaoRate_value) {
            $lowZhibiao[] = 'target'.substr($asortZhibiaoRate_value, 7);// 截取tarrate后的字符
        }

        // 筛选因子名称
        $factorNames = \arcms\lib\model\ValueFactorName::model()->getDb()->queryAll();

        foreach ($factorNames as &$factorName) {
            $newFactorName[$factorName['factor']] = $factorName['name'];
        }
        // 筛选评语
        $factorReviews = \arcms\lib\model\ValueFactorReview::model()->getDb()->queryAll();

        foreach ($factorReviews as &$factorReview) {
            $newFactorReview[$factorReview['factor']] = $factorReview['review'];
        }
// var_dump($factorNames, $newFactorName);
// exit('factorNames, newFactorName');
// var_dump($factorReviews, $newFactorReview);
// exit('factorReviews, newFactorReview');

        // 市值估值预测
        $evaluationForecast = \arcms\lib\model\ValueEvaluationForecast::model()->getDb()
            ->where(['report_num' => $num])
            ->queryAll();

        // 市值类别和评语
        $evaluationTypeReview = \arcms\lib\model\ValueEvaluationTypeReview::model()->getDb()
            ->where(['id' => $companyScore['evaluation_type_id']])
            ->queryRow();

        // 把市值（元）转化成人民币大写形式
        $RMB = $this->rmb_format($companyScore["shizhi"]);

        // 市值指标预测的评语
        $evaluationForecastNote = \arcms\lib\model\ValueEvaluationForecastNote::model()
            ->getDb()
            ->queryAll();
        foreach ($evaluationForecastNote as $evaluationForecastNote_value) {
            $forecastNote[] = $evaluationForecastNote_value['content'];
        }

        // P/E，P/B和PEG为负数的，显示“-”，保留两位小数
        $evaluationForecast[0]["p/e"] = ((sprintf("%.2f", $evaluationForecast[0]["p/e"])) > 0) ? sprintf("%.2f", $evaluationForecast[0]["p/e"]) : "-";
        $evaluationForecast[0]["p/b"] = ((sprintf("%.2f", $evaluationForecast[0]["p/b"])) > 0) ? sprintf("%.2f", $evaluationForecast[0]["p/b"]) : "-";
        $evaluationForecast[0]["peg"] = ((sprintf("%.2f", $evaluationForecast[0]["peg"])) > 0) ? sprintf("%.2f", $evaluationForecast[0]["peg"]) : "-";

        $evaluationForecast[1]["p/e"] = ((sprintf("%.2f", $evaluationForecast[1]["p/e"])) > 0) ? sprintf("%.2f", $evaluationForecast[1]["p/e"]) : "-";
        $evaluationForecast[1]["p/b"] = ((sprintf("%.2f", $evaluationForecast[1]["p/b"])) > 0) ? sprintf("%.2f", $evaluationForecast[1]["p/b"]) : "-";
        $evaluationForecast[1]["peg"] = ((sprintf("%.2f", $evaluationForecast[1]["peg"])) > 0) ? sprintf("%.2f", $evaluationForecast[1]["peg"]) : "-";

        $evaluationForecast[2]["p/e"] = ((sprintf("%.2f", $evaluationForecast[2]["p/e"])) > 0) ? sprintf("%.2f", $evaluationForecast[2]["p/e"]) : "-";
        $evaluationForecast[2]["p/b"] = ((sprintf("%.2f", $evaluationForecast[2]["p/b"])) > 0) ? sprintf("%.2f", $evaluationForecast[2]["p/b"]) : "-";
        $evaluationForecast[2]["peg"] = ((sprintf("%.2f", $evaluationForecast[2]["peg"])) > 0) ? sprintf("%.2f", $evaluationForecast[2]["peg"]) : "-";

        $evaluationForecast[3]["p/e"] = ((sprintf("%.2f", $evaluationForecast[3]["p/e"])) > 0) ? sprintf("%.2f", $evaluationForecast[3]["p/e"]) : "-";
        $evaluationForecast[3]["p/b"] = ((sprintf("%.2f", $evaluationForecast[3]["p/b"])) > 0) ? sprintf("%.2f", $evaluationForecast[3]["p/b"]) : "-";
        $evaluationForecast[3]["peg"] = ((sprintf("%.2f", $evaluationForecast[3]["peg"])) > 0) ? sprintf("%.2f", $evaluationForecast[3]["peg"]) : "-";

        $evaluationForecast[4]["p/e"] = ((sprintf("%.2f", $evaluationForecast[4]["p/e"])) > 0) ? sprintf("%.2f", $evaluationForecast[4]["p/e"]) : "-";
        $evaluationForecast[4]["p/b"] = ((sprintf("%.2f", $evaluationForecast[4]["p/b"])) > 0) ? sprintf("%.2f", $evaluationForecast[4]["p/b"]) : "-";
        $evaluationForecast[4]["peg"] = ((sprintf("%.2f", $evaluationForecast[4]["peg"])) > 0) ? sprintf("%.2f", $evaluationForecast[4]["peg"]) : "-";

        $evaluationForecast[5]["p/e"] = ((sprintf("%.2f", $evaluationForecast[5]["p/e"])) > 0) ? sprintf("%.2f", $evaluationForecast[5]["p/e"]) : "-";
        $evaluationForecast[5]["p/b"] = ((sprintf("%.2f", $evaluationForecast[5]["p/b"])) > 0) ? sprintf("%.2f", $evaluationForecast[5]["p/b"]) : "-";
        $evaluationForecast[5]["peg"] = ((sprintf("%.2f", $evaluationForecast[5]["peg"])) > 0) ? sprintf("%.2f", $evaluationForecast[5]["peg"]) : "-";

        // 注册地址，排除直辖市无省份归属的问题
        if (strpos($companyInfo["province"], "市") > 0) {
            $companyInfo["zcdz"] = $companyInfo["city"].$companyInfo["area"];
            echo "直辖市<br>";
        }else{
            $companyInfo["zcdz"] = $companyInfo["province"].$companyInfo["city"].$companyInfo["area"];
            echo "省份<br>";
        }

        // 查看value_system 表信息，包含联系电话、地址等
        $valueSystems = \arcms\lib\model\ValueSystems::model()->getDb()->queryRow();


        $html = '
        <head>
            <meta charset="utf-8">
            <style type="text/css">
                p{
                    text-indent:2em;
                    line-height:150%;
                    margin:0 auto;
                    font-size: 12pt;
                    text-align:justify;
                    text-justify:inter-ideograph;
                    font-family:楷体_GB2312;
                }
                h4{
                    font-size: 14pt;
                    font-family:楷体_GB2312;
                }
                
                span{
                    font-size: 12pt;
                    font-family:楷体_GB2312;
                }
                
                .fubiao1{
                    padding: 0em .5em; 
                    border-bottom: solid 1px black; 
                    text-align:left;
                }
                canvas{
                }
                .caiwu{
                    text-align:justify;
                    border-top:1px solid black;
                }
                
            </style>
        </head>
        <body>
            <div style="font-family:楷体_GB2312;">
                <h2 align="center" style="margin:150px auto 5px auto; font-size:16pt;">大数据智能市值评估报告</h2>
                <h2 align="center" style="margin:5px auto 160px auto; font-size:16pt;">（快速版）</h2>
                <div>
                    <ul style="list-style:none; margin:10px; padding:10px;>
                        <li style="display:inline; line-height:20px; margin:10px; list-style-type: none;">
                        <span style="font-size: 14pt; font-weight:bold; ">评估标的：</span><span style="font-size: 14pt;">'.$companyInfo["name"].'</span></li>
                    </ul>              
                    <ul style="list-style:none; margin:10px; padding:10px 10px 300px 10px;>
                        <li style="display:inline; line-height:20px; margin:10px; list-style-type: none;">
                            <span style="font-size: 14pt; font-weight:bold; ">报告编号：</span><span style="font-size: 14pt;">【'.substr($summaryReport["num"],0,4).'】第'.substr($summaryReport["num"],-4).'号</span></li>
                    </ul>
                </div>
                
                
                
                <div style="margin:400px auto 2px auto;">
                    <ul style="list-style:none; margin:10px; padding:10px;>
                        <li style="list-style-type: none;">
                            <p align="center" style="line-height:200% ; text-align:center; margin-top:10px; padding:10px; font-size: 14pt;" text-align="center">'.$summaryReport["evaluation_agency"].'</p>
                        </li>
                    </ul>
                    <ul style="list-style:none; margin:10px; padding:10px;>
                        <li style="list-style-type: none;">
                            <span align="center" style="line-height:200% ; text-align:center; margin-top:10px; padding:10px; font-size: 14pt;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.date("Y年m月d日", time()).'</span>
                        </li>
                    </ul>
                </div>
        
        
                <br clear=all style="page-break-before:always" mce_style="page-break-before:always">

                <h4 align="center" style="letter-spacing:20px;">声明</h4>
                <br><br>
                <p>本系统技术人员于整体评估过程，恪守独立、客观和公正的原则，严格遵循有关法律、法规和市值评估理论，并尽专业上应有之注意，且对下列评估事项进行声明：</p>
                <p>1、本报告所使用之评估标的的预测信息，为评估委任人所提供，委托人需提供必要的资料并保证所提供资料的真实性、合法性、完整性，恰当使用评估报告是委托方和相关当事方的责任。</p>
                <p>2、评估报告中的分析、判断和结论受评估报告中假设和限定条件的限制，评估报告使用者应当充分考虑评估报告中载明的假设，限定条件、特别事项说明及其对评估结论的影响。</p>
                <p>3、本系统评估目的是对评估对象进行成长性评价和智能估值，对评估对象法律权属确认或发表意见超出评估团队的评估范围。本评估报告不对评估对象的法律权属提供任何保证。</p>
                <p>4、此报告仅供委托人内部管理决策参考，评估团队对委托人之任何筹资决策不负任何法律责任。</p>
                <p>5、本报告内容非经委托人书面同意，不得进行复印或以任何方式将内容传递于第三人。本报告及结论，仅能基于管理目的使用，不得移做其他目的使用。</p>
                <p>6、本评估系统主要采用大数据智能化进行科技型中小企业估值，如需更精准的估值服务，建议咨询我们的线下服务。</p>

                <br clear=all style="page-break-before:always" mce_style="page-break-before:always">

                <h4 style="text-align:center; font-family:楷体_GB2312; font-size: 16pt; padding-bottom:400px;">'.$companyInfo["name"].'</h4>
                <div style="margin-top:100px;">
                <table style="font-family:楷体_GB2312; border:0;">
                    <tbody>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">法人代表：</td>
                            <td style="display:inline;">'.$companyInfo["legal_person"].'</td>
                        <tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">成立日期：</td>
                            <td style="display:inline;">'.$companyInfo["establish_year"].'</td>
                        <tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">注册资本：</td>
                            <td style="display:inline;">'.$companyInfo["capital"].'万</td>
                        <tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">注册地址：</td>
                            <td style="display:inline;">'.$companyInfo["zcdz"].'</td>
                        <tr>
                        <tr>
                            <td colspan="2" style="display:inline; padding:0.5em 0.2em;" valign="top">统一社会信用代码：'.$companyInfo["social_credit_code"].'</td>
                        <tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">联系电话：</td>
                            <td style="display:inline;">'.$companyInfo["linkphone"].'</td>
                        <tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">电子邮箱：</td>
                            <td style="display:inline;">'.$companyInfo["email"].'</td>
                        <tr>
                         <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">所属行业：</td>
                            <td style="display:inline;">'.$companyInfo["hangye"].'</td>
                        </tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">发展阶段：</td>
                            <td style="display:inline;">'.$companyInfo["jieduan"].'</td>
                        </tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">员工人数：</td>
                            <td style="display:inline;">'.$companyInfo["employees"].'人</td>
                        </tr>
                        <tr>
                            <td style="display:inline; padding:0.5em 0.2em; width:18%;" valign="top">经营范围：</td>
                            <td style="display:inline;">'.$companyInfo["main_business"].'</td>
                        <tr>
                    </tbody>
                </table>
                </div>

                <br clear=all style="page-break-before:always" mce_style="page-break-before:always">

                <h4 style="text-align:center; margin:20px auto 40px auto;">第一部分 企业基本情况</h4>
                <h4 style="margin:30px auto 20px auto;">一、企业简介</h4>
                <p>'.$companyInfo["introduce"].'</p>
                <h4 style="margin:20px auto 20px auto;">二、主要产品</h4>
                <p>'.$companyInfo["products"].'</p>
                <h4 style="margin:20px auto 20px auto;">三、市场分布</h4>
                <p>'.$companyInfo["business_area"].'</p>
                <h4 style="margin:20px auto 20px auto;">四、商业模式</h4>
                <p>'.$companyInfo["business_model"].'</p>

                <h4 style="margin:20px auto 10px auto;">五、科技创新</h4>
                <div style="margin:10px auto 20px 40px;">
                <table style="font-family:楷体_GB2312;" border="0" cellspacing="0" cellpadding="0">
                    <tbody style="font-family:楷体_GB2312">
                        <tr>
                            <td style="padding:0.5em 0.5em;">科研人员占比：'.$companyInfo["kyryzb"].'</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5em 0.5em;">知识产权：共'.$companyInfo["zscq"].'项，其中发明专利：'.$companyInfo["patent"].'项，实用新型：'.$companyInfo["shiyong"].'项，外观设计：'.$companyInfo["design"].'项，软件著作权：'.$companyInfo["soft"].'项</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5em 0.5em;">产品级别（国家级/行业级/省级/市级）：'.$companyInfo["cpjb"].'</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5em 0.5em;">产品是否被列入（“863”计划/“火炬计划”/重点新产品）：'.$companyInfo["cpsfblr"].'</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5em 0.5em;">是否有正在研发的产品或技术：'.$companyInfo["zzyf"].'</td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <h4>六、财务状况</h4>
                <p style="text-indent:0em;">1、公司近三年财务状况(单位：万元)</p>
                <div style="margin:5px auto;">
                <table style="font-family:楷体_GB2312; padding-bottom:10px; text-align:justify; width:100%;">
                    <tbody>
                        <tr>
                            <td class="caiwu"></td>
                            <td class="caiwu">'.($lastyear-2).'</td>
                            <td class="caiwu">'.($lastyear-1).'</td>
                            <td class="caiwu">'.$lastyear.'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">资产</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["assets"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["assets"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["assets"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">&nbsp; 流动资产</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["current_assets"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["current_assets"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["current_assets"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">&nbsp; 存货</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["stock"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["stock"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["stock"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">负债</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["liabilities"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["liabilities"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["liabilities"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">&nbsp; 流动负债</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["current_liabilities"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["current_liabilities"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["current_liabilities"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">主营业收入</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["main_operating_income"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["main_operating_income"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["main_operating_income"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">应收账款</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["accounts_receivable"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["accounts_receivable"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["accounts_receivable"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu">研发费用</td>
                            <td class="caiwu">'.number_format($financialDatas[0]["r_d_expenses"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[1]["r_d_expenses"]).'</td>
                            <td class="caiwu">'.number_format($financialDatas[2]["r_d_expenses"]).'</td>
                        </tr>
                        <tr>
                            <td class="caiwu caiwu_bottom" style="border-bottom:1px solid black;">税后净利润</td>
                            <td class="caiwu caiwu_bottom" style="border-bottom:1px solid black;">'.number_format($financialDatas[0]["net_profit"]).'</td>
                            <td class="caiwu caiwu_bottom" style="border-bottom:1px solid black;">'.number_format($financialDatas[1]["net_profit"]).'</td>
                            <td class="caiwu caiwu_bottom" style="border-bottom:1px solid black;">'.number_format($financialDatas[2]["net_profit"]).'</td>
                        </tr>
                    </tbody>
                </table>
                </div>
                
                <p style="text-indent:0em;">2、收益预测</p>
                <p>未来一个会计年度净利润（单位：万元）:'.number_format($financialForecast["forecast1"]).'</p>
                <p>预测未来三年业绩平均增长率：'.($financialForecast["R"]*100).'%</p>

                <br clear=all style="page-break-before:always" mce_style="page-break-before:always">

                <h4 style="text-align:center; padding-bottom:10px;">第二部分 成长性智能评价</h4>
                <h4>一 、指标选择与评价方法</h4>
                <p style="text-indent:0;">1、 指标选取原则</p>
                <p>（1） 财务指标与非财务指标项结合；</p>
                <p>（2） 定量指标和定性指标结合；</p>
                <p>（3） 科学全面兼顾企业的高科技性、高成长性。</p>
                <p style="text-indent:0;">2、评价方法</p>
                <p>综合运用矩阵分析，多目标决策法（AHP），隶属函数评估法，加权平均法，功效系数法，空间计分法。</p>

                <h4>二 、评价结果</h4>
                <p>采用系统构建的评估模型，结合委托人提供的数据并结合同行业数据信息，采用百分制打分方法，分数平均化后，得出'.$companyInfo["name"].'成长性总得分为'.sprintf("%.2f", $companyScore["growth"]).'分。</p>
                <h4 align="center">成长性评价报告表</h4>
                
                <div style="margin-bottom:10px;">
                <table style="width: 100%; font-size: 12pt; border-collapse: collapse; margin:auto; font-family:楷体_GB2312; text-align:center;">
                    <tbody>
                        <tr>
                            <td class="fubiao1" rowspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black; text-align:center;">目标层</td>
                            <td class="fubiao1" colspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black; text-align:center;">维度层</td>                    
                            <td class="fubiao1" colspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black; text-align:center;">指标层</td>
                            <td class="fubiao1" colspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black; text-align:center;">因子层</td>                   
                        </tr>
                        <tr>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">评价指标</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">得分</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">评价指标</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">得分</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">评价指标</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">得分</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="43" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor_all"]).'</td>
                            <td class="fubiao1" rowspan="10">'.$newFactorName["dime1"].'</td>
                            <td class="fubiao1" rowspan="10" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime1"]).'</td>
                            <td class="fubiao1" rowspan="2">'.$newFactorName["target1"].'</td>
                            <td class="fubiao1" rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target1"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor1"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor1"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor2"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor2"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target2"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target2"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor3"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor3"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor4"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor4"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor5"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor5"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="2">'.$newFactorName["target3"].'</td>
                            <td class="fubiao1" rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target3"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor6"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor6"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor7"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor7"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target4"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target4"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor8"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor8"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor9"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor9"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor10"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor10"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="11">'.$newFactorName["dime2"].'</td>
                            <td class="fubiao1" rowspan="11" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime2"]).'</td>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target5"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target5"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor11"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor11"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;  text-align:left;">'.$newFactorName["factor12"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor12"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;  text-align:left;">'.$newFactorName["factor13"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor13"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="4">'.$newFactorName["target6"].'</td>
                            <td class="fubiao1" rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target6"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor14"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor14"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor15"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor15"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor16"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor16"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor17"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor17"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="4">'.$newFactorName["target7"].'</td>
                            <td class="fubiao1" rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target7"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor18"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor18"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor19"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor19"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor20"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor20"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor21"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor21"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="8">'.$newFactorName["dime3"].'</td>
                            <td class="fubiao1" rowspan="8" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime3"]).'</td>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target8"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target8"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor22"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor22"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor23"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor23"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor24"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor24"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="2">'.$newFactorName["target9"].'</td>
                            <td class="fubiao1" rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target9"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor25"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor25"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor26"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor26"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target10"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target10"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor27"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor27"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor28"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor28"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor29"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor29"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="6">'.$newFactorName["dime4"].'</td>
                            <td class="fubiao1" rowspan="6" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime4"]).'</td>
                            <td class="fubiao1" rowspan="2">'.$newFactorName["target11"].'</td>
                            <td class="fubiao1" rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target11"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor30"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor30"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor31"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor31"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="4">'.$newFactorName["target12"].'</td>
                            <td class="fubiao1" rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target12"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor32"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor32"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor33"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor33"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor34"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor34"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor35"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor35"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="8">'.$newFactorName["dime5"].'</td>
                            <td class="fubiao1" rowspan="8" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime5"]).'</td>
                            <td class="fubiao1" rowspan="2">'.$newFactorName["target13"].'</td>
                            <td class="fubiao1" rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target13"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor36"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor36"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor37"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor37"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target14"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target14"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor38"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor38"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor39"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor39"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor40"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor40"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1" rowspan="3">'.$newFactorName["target15"].'</td>
                            <td class="fubiao1" rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target15"]).'</td>
                            <td class="fubiao1">'.$newFactorName["factor41"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor41"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor42"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor42"]).'</td>
                        </tr>
                        <tr>
                            <td class="fubiao1">'.$newFactorName["factor43"].'</td>
                            <td class="fubiao1" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor43"]).'</td>
                        </tr>
                    </tbody>
                </table>
                </div>
                
                <p>按照该系统得分等级可知，'.$companyInfo["name"].'属于'.$starReview["review"].'的企业。其投资价值主要表现在'.$starReview["investment_value"].'</p>

                <p>但结合更加详细的二级指标分析可以看出，'.$newFactorName[$lowZhibiao[0]].'较低，同时'.$newFactorName[$lowZhibiao[1]].'、'.$newFactorName[$lowZhibiao[2]].'、'.$newFactorName[$lowZhibiao[3]].'得分较低。结合更加详细的三级指标可以看出企业的问题所在。</p>
                <p>'.$newFactorReview[$lowYinzi[0]].'</p>
                <p>'.$newFactorReview[$lowYinzi[1]].'</p>
                <p>'.$newFactorReview[$lowYinzi[2]].'</p>
                <p>'.$newFactorReview[$lowYinzi[3]].'</p>
                <p>'.$newFactorReview[$lowYinzi[4]].'</p>
                <p>'.$newFactorReview[$lowYinzi[5]].'</p>


                <br clear=all style="page-break-before:always" mce_style="page-break-before:always">

                <h4 style="text-align:center; margin-bottom:20px;">第三部分 估值信息</h4>

                <h4>一、评估价值定义</h4>
                <p>评估的价值类型实际上是评估行为所基于的一系列可能存在的各种明显的或隐含的假设及前提，这些假设及前提往往决定了评估方法的选择和运用以及对评估结果的正确理解。根据本次评估的目的和评估对象，我们选用的价值类型为市场价值。</p>
                <p>评估基准日为'.$summaryReport["base_date"].'。</p>

                <h4>二、评估方法</h4>
                <p>该智能市值评估是采用人工智能、机器学习、统计学和数据库的交叉方法，整合总体、产业和企业经济数据及文字，汇入 BD 模型（即 Big-Data）,筛选出判定系数 R-Square最高的市值预测模型来进行企业估值。评估方法有：</p>


                <p>（1）市场比较法：主要与同行业上市公司和三板挂牌企业作为评估参照对象，提取样本数据，得出相对估值。</p>
                <p>（2）时间序列预测法：包括时间序列模型（ARIMA模型，EVA），误差修正模型，VAR风险测量模型，预测企业的内在价值。</p>
                <p>无论是采用统计学预测模型还是选取调整优化的市场比较法模型来估值，通常要针对被评估公司的内在特征进行调整，以保证估值的准确性和合理性。</p>

                

                <h4>三 、 评估依据和范围</h4>
                <p>1、评估工作数据范围</p>
                <p>（1）宏观经济增长数据，如GDP增长率，通货增长率等；</p>
                <p>（2）行业估值指标以及财务指标；</p>
                <p>（3）同行业上市A股公司（剔除ST）及三板企业的信息。</p>

                <p>2、评估工作的取价依据</p>
                <p>（1）现行市场价格信息，如银行利率、证券市场国债交易价格等；</p>
                <p>（2）委托方提供的收益预测；</p>
                <p>（3）其他相关取价标准。</p>

                <h4>四、评估基准和假设</h4>
                <p>1、评估基准</p>
                <p>对'.$companyInfo["name"].'的市值评估是根据公司以前年度实际的生产经营基础和潜力以及公司的经营业绩及各项经济指标，考虑公司持续经营的假设前提，并遵循国家现行法律、法规的有关规定，本着客观求实的原则，对公司的市值进行预测评估。</p>
                <p>2、预测的假设条件</p>
                <p>（1）一般假设</p>
                <p>①针对评估基准日时资产的实际状况，假设'.$companyInfo["name"].'能持续经营；</p>
                <p>②假设'.$companyInfo["name"].'的经营者是负责的，且公司管理层有能力担当其职务；</p>
                <p>③除非另有说明，假设'.$companyInfo["name"].'完全遵守所有有关的法律和法规；</p>
                <p>④假设委托人提供的历年财务资料所采取的会计政策和编写此份报告时所采用的会计政策在重要方面基本一致。</p>

                <p>（2）特殊假设</p>
                <p>①'.$companyInfo["name"].'所在地及中国的社会经济环境不产生大的变更，所遵循的国家现行法律、法规、制度及社会政治和经济政策与现时无重大变化；</p>
                <p>②假设在现有的运作方式和管理水平的基础上，将保持持续性经营，并在经营范围、方式上与现时方向保持一致；</p>
                <p>③假设'.$companyInfo["name"].'资产使用效率得到有效发挥；</p>
                <p>④有关信贷利率、汇率、赋税基准及税率，政策性征收费用等不发生重大变化；</p>
                <p>⑤产成品或服务的社会需求将保持一定幅度增长;</p>
                <p>⑥假设折现年限内将不会遇到重大的销售货款回收方面等坏账问题;</p>
                <p>⑦无其他人力不可抗拒因素及不可预见因素对企业造成重大不利影响。</p>
                <p>系统根据市值评估的要求，认定这些前提条件在评估基准日时成立，当未来经济环境发生较大变化时，将不承担由于前提条件的改变而推导出不同评估结果的责任。
                </p>

                <h4>五、评估结果</h4>
                <p>根据委托人所提供的'.$companyInfo["name"].'基本信息，以及结合A股上市公司，新三板公司行业信息，智能市值评估系统的分析结果如下：</p>
                <p >'.$companyInfo["name"].'评估基准日的股权市值为：'.number_format($companyScore["shizhi"]).'元，大写人民币：'.$RMB.'。</p>

                <h4 align="center">市值及估值指标预测表</h4>
                <table style="width: 100%; font-size: 12pt; border-collapse: collapse; margin:auto; font-family:楷体_GB2312; text-align:center;">
                    <tbody>
                        <tr>
                            <td style="border-top:1px solid black; padding: .5em .5em;"></td>
                            <td style="border-top:1px solid black; padding: .5em .5em;">市值</td>
                            <td style="border-top:1px solid black; padding: .5em .5em;">每股盈余</td>
                            <td style="border-top:1px solid black; padding: .5em .5em;">P/E</td>
                            <td style="border-top:1px solid black; padding: .5em .5em;">P/B</td>
                            <td style="border-top:1px solid black; padding: .5em .5em;">PEG</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">期初</td>
                            <td style="padding: .5em .5em;">'.number_format(round($evaluationForecast[0]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[0]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[0]["p/e"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[0]["p/b"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[0]["peg"].'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">'.(date("Y", time())).'E</td>
                            <td style="padding: .5em .5em;">'.number_format(round($evaluationForecast[1]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[1]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[1]["p/e"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[1]["p/b"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[1]["peg"].'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">'.(date("Y", time())+1).'E</td>
                            <td style="padding: .5em .5em;">'.number_format(round($evaluationForecast[2]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[2]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[2]["p/e"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[2]["p/b"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[2]["peg"].'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">'.(date("Y", time())+2).'E</td>
                            <td style="padding: .5em .5em;">'.number_format(round($evaluationForecast[3]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[3]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[3]["p/e"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[3]["p/b"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[3]["peg"].'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">'.(date("Y", time())+3).'E</td>
                            <td style="padding: .5em .5em;">'.number_format(round($evaluationForecast[4]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[4]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[4]["p/e"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[4]["p/b"].'</td>
                            <td style="padding: .5em .5em;">'.$evaluationForecast[4]["peg"].'</td>
                        </tr>
                        <tr>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.(date("Y", time())+4).'E</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.number_format(round($evaluationForecast[5]["p"])).'元</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[5]["eps"]).'元</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.$evaluationForecast[5]["p/e"].'</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.$evaluationForecast[5]["p/b"].'</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.$evaluationForecast[5]["peg"].'</td>
                        </tr>
                    </tbody>
                </table>
                <p>注：</p>
                <p>1、'.$forecastNote[0].'</p>
                <p>2、'.$forecastNote[1].'</p>
                

                <br clear=all style="page-break-before:always" mce_style="page-break-before:always">

                <h4 style="text-align:center;">第四部分 建议</h4>
                
                <div style="margin:10px auto; font-family:楷体_GB2312;">
                    <p>一、该报告是基于委托人线上提供的资料和数据得出的成长性及估值结果，建议企业咨询我们的线下服务。专业化团队进驻企业，企业可定制估值报告，获取更加专业的企业管理咨询及估值诊断服务。</p>
                    
                    <p>二、中企艾维是由云石资本为主导、四川省生产力促进中心全力支持的专注于科技型中小企业的金融服务平台，帮助科技型中小企业的解决一系列的发展难题。服务内容有：</p>
                    <p>1、运营顾问服务：企业估值、商业模式创新、商业计划书写作、顶层设计、财税及法律服务；</p>
                    <p>2、融资对接：为企业融资提供整体服务，包括编制融资材料和方案，帮助联络和筛选合格投资者，组织谈判和相关合同起草等；</p>
                    <p>3、兼并收购：发现潜在的有兼并需求的公司及收购对象、制定收购或兼并计划、交易架构设计等相关咨询服务；</p>
                    <p>4、挂牌及上市辅导：解读和交流政策、组织和协调中介机构，提供包括尽职调查、方案设计、目标市场选择、交易结构和股权融资安排等全部相关专业服务；</p>
                    <p>5、项目补贴等综合金融服务：帮助科技型中小企业申请国家项目补贴、办理军工资质认证以及专业培训等一系列金融服务业务。</p>
                </div>
                <div style="margin-top:20px; font-family:楷体_GB2312;">
                    <p style="font-weight:bold; margin:10px auto;">成都中企艾维数据有限公司</p>
                    <table style="margin-left:32px; font-family:楷体_GB2312;"> 
                        <tr>
                            <td style="width:20%;">联系电话：</td>
                            <td>'.$valueSystems["service_call"].'</td>
                        </tr>
                        <tr>
                            <td style="width:20%;">E-mail：</td>
                            <td>'.$valueSystems["email"].'</td>
                        </tr>
                        <tr>
                            <td style="width:20%;">地址</td>
                            <td>'.$valueSystems["address"].'</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top:20px; font-family:楷体_GB2312;">
                    <p style="font-weight:bold; margin:10px auto;">四川省生产力促进中心</p>
                    <table style="margin-left:32px; font-family:楷体_GB2312;"> 
                        <tr>
                            <td style="width:20%;">联系电话：</td>
                            <td>'.$valueSystems["service_call"].'</td>
                        </tr>
                        <tr>
                            <td style="width:20%;">E-mail：</td>
                            <td>'.$valueSystems["email"].'</td>
                        </tr>
                        <tr>
                            <td style="width:20%;">地址</td>
                            <td>'.$valueSystems["address"].'</td>
                        </tr>
                    </table>
                </div>
            </div>
        </body>
    '; 

    define('ARCMS_PATH', dirname(dirname(dirname(dirname(__FILE__)))));       
    require_once(ARCMS_PATH."/lib/ext/Word/Word.class.php");
 
    $word = new \word(); 
    $word->start(); 
    
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    if (strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')) 
    {
        header('Content-Disposition: attachment; filename=评估报告.doc');
    }else if (strpos($_SERVER["HTTP_USER_AGENT"],'Firefox')) {
        Header('Content-Disposition: attachment; filename=评估报告.doc');
    } else {
        header('Content-Disposition: attachment; filename=评估报告.doc');
    }

    header("Pragma:no-cache");
    header("Expires:0");
    
   
    echo $html; 

    ob_end_flush();//输出全部内容到浏览器

    
    }

    /** 
     * 人民币小写转大写 
     * 
     * @param string $number 数值 
     * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆" 
     * @param bool $is_round 是否对小数进行四舍五入 
     * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30
     * @return string 
     */
    function rmb_format($money = 0, $int_unit = '元', $is_round = true, $is_extra_zero = false) 
    {
        if ($money < 0) {
            $tmp_money = -1;
            $money = abs($money);
        } else {
            $tmp_money = 1;
        }
        
        // 将数字切分成两段 
        $parts = explode ( '.', $money, 2 );
        $int = isset ( $parts [0] ) ? strval ( $parts [0] ) : '0';
        $dec = isset ( $parts [1] ) ? strval ( $parts [1] ) : '';
     
        // 如果小数点后多于2位，不四舍五入就直接截，否则就处理 
        $dec_len = strlen ( $dec );
        if (isset ( $parts [1] ) && $dec_len > 2) {
            $dec = $is_round ? substr ( strrchr ( strval ( round ( floatval ( "0." . $dec ), 2 ) ), '.' ), 1 ) : substr ( $parts [1], 0, 2 );
        }
 
        // 当number为0.001时，小数点后的金额为0元 
        if (empty ( $int ) && empty ( $dec )) {
            return '零';
        }
     
        // 定义 
        $chs = array ('0', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖' );
        $uni = array ('', '拾', '佰', '仟' );
        $dec_uni = array ('角', '分' );
        $exp = array ('', '万' );
        $res = '';
 
        // 整数部分从右向左找 
        for($i = strlen ( $int ) - 1, $k = 0; $i >= 0; $k ++) {
            $str = '';
            // 按照中文读写习惯，每4个字为一段进行转化，i一直在减 
            for($j = 0; $j < 4 && $i >= 0; $j ++, $i --) {
                $u = $int {$i} > 0 ? $uni [$j] : ''; // 非0的数字后面添加单位 
                $str = $chs [$int {$i}] . $u . $str;
            }
            $str = rtrim ( $str, '0' ); // 去掉末尾的0 
            $str = preg_replace ( "/0+/", "零", $str ); // 替换多个连续的0 
            if (! isset ( $exp [$k] )) {
                $exp [$k] = $exp [$k - 2] . '亿'; // 构建单位 
            }
            $u2 = $str != '' ? $exp [$k] : '';
            $res = $str . $u2 . $res;
        }
 
        // 如果小数部分处理完之后是00，需要处理下 
        $dec = rtrim ( $dec, '0' );
        // var_dump ( $dec );
        // 小数部分从左向右找 
        if (! empty ( $dec )) {
            $res .= $int_unit;
     
            // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求 
            if ($is_extra_zero) {
                if (substr ( $int, - 1 ) === '0') {
                    $res .= '零';
                }
            }
     
            for($i = 0, $cnt = strlen ( $dec ); $i < $cnt; $i ++) {                  
                $u = $dec {$i} > 0 ? $dec_uni [$i] : ''; // 非0的数字后面添加单位 
                $res .= $chs [$dec {$i}] . $u;
                if ($cnt == 1)
                    $res .= '整';
            }
     
            $res = rtrim ( $res, '0' ); // 去掉末尾的0 
            $res = preg_replace ( "/0+/", "零", $res ); // 替换多个连续的0 
        } else {
            $res .= $int_unit . '整';
        }
        if ($tmp_money == -1) {
            return "负".$res;
        } else {
            return $res;
        }
        
    }

}

