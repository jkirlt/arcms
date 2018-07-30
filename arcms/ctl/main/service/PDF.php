<?php
namespace arcms\ctl\main\service;
class PDF
{
    public function downAsPdf($companyid, $uid, $num)
    {
        define('ORI_RATH_PDF', dirname(dirname(dirname(dirname(__FILE__)))));
        require_once(ORI_RATH_PDF.'/lib/ext/TCPDF-master/tcpdf.php');

        // 查询评估报告编号等信息，用以写入pdf
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

        // 知识产权
        $companyInfo["zscq"]=$companyInfo["patent"]+$companyInfo["shiyong"]+$companyInfo["design"];

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

        // 财务状况 yxf
        $lastyear = date('Y', time()) - 1;  // 最近一年的年份
        $financialData = \arcms\lib\model\ValueFinancialData::model()->getDb()
            ->where(['report_num' => $num, 'years' => $lastyear])
            ->queryRow();

        // 财务预测 yxf
        $financialForecast = \arcms\lib\model\ValueFinancialForecast::model()->getDb()
            ->where(['report_num' => $num])
            ->queryRow();


        // 企业评估得分
        $companyScore = \arcms\lib\model\ValueCompanyScore::model()->getDb()
            ->where(['report_num' => $num])
            ->queryRow();
        $companyScore['shizhi'] = sprintf("%.2f", $companyScore['value']*10000*10000);


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

        switch ($companyInfo['stage_id']) {  // 企业处于初创期时，没有成长能力的4个因子，去除
            case '1':
                unset($yinziRate['rate18'], $yinziRate['rate19'], $yinziRate['rate20'], $yinziRate['rate21'] );
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
        $factorName = \arcms\lib\model\ValueFactorName::model()->getDb()->queryRow();

        // 筛选评语
        $factorReview = \arcms\lib\model\ValueFactorReview::model()->getDb()->queryRow();

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
        

        //实例化 
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); 
         
        // 设置文档信息 
        $pdf->SetCreator('成都中企艾维数据有限公司'); 
        $pdf->SetAuthor('yaoxf'); 
        $pdf->SetTitle('成都中企艾维数据有限公司'); 
        $pdf->SetSubject('TCPDF Tutarcmsal'); 
        $pdf->SetKeywords('TCPDF, PDF, PHP'); 
 
        // 设置页眉和页脚信息 
        //$pdf->SetHeaderData('logo.png', 30, 'Helloweba.com', '致力于WEB前端技术在中国的应用', array(0,64,255), array(0,64,128)); 
        $pdf->SetHeaderData('', 30, '', '成都中企艾维数据有限公司', array(0,64,255), array(0,64,128)); 
        $pdf->setFooterData(array(0,64,0), array(0,64,128)); 
         
        // 设置页眉和页脚字体 
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10')); 
        $pdf->setFooterFont(Array('helvetica', '', '8')); 
         
        // 设置默认等宽字体 
        $pdf->SetDefaultMonospacedFont('courier'); 
         
        // 设置间距 
        $pdf->SetMargins(15, 27, 15); 
        $pdf->SetHeaderMargin(5); 
        $pdf->SetFooterMargin(10); 
 
        // 设置分页 
        $pdf->SetAutoPageBreak(TRUE, 25); 
         
        // set image scale factor 
        $pdf->setImageScale(1.25); 
         
        // set default font subsetting mode 
        $pdf->setFontSubsetting(true); 
         
        //设置字体 
        $pdf->SetFont('stsongstdlight', '', 14); 
         
        $pdf->AddPage(); 

        $html = '
            <head>
                <meta charset="utf-8">
            </head>
            <body>
            <div>
                <h2 align="center" style="margin:80px auto 550px auto; font-size:16pt;">大数据智能市值评估报告</h2>
                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                 <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                  <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                   <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <div style="margin-top:300px; padding-top:300px; ">
                    <br><br><br><br><br><br> <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                    <p></p>
                    <span>评估标的：</span>'.$companyInfo["name"];

                    $pdf->writeHTML($html, true, false, true, false, "");

                    $html = '
                    <span>市值评估基准日：</span>'.$summaryReport["base_date"];
                    $pdf->writeHTML($html, true, false, true, false, "");

                    $html = '
                    <span>报告编号：</span>'.$summaryReport["num"];
                    $pdf->writeHTML($html, true, false, true, false, "");

                    $html = '
                    <span>评估机构：</span>'.$summaryReport["evaluation_agency"];
                    $pdf->writeHTML($html, true, false, true, false, "");

                    $html = '<br><br><br><br><span align="center"> '.date("Y年m月d日", time());
                    $pdf->writeHTML($html, true, false, true, false, "");

                    $html = '</span>
                </div>';
        
                $pdf->writeHTML($html, true, false, true, false, "");

                $pdf->AddPage(); 
        
                $html = '
                <h4 align="center" style="letter-spacing:20px; font-size: 14pt; font-family:楷体_GB2312;">声明</h4><br><br>';

                $pdf->writeHTML($html, true, false, true, false, "");
                $html = '<p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312; ">      本系统技术人员于整体评估过程，恪守独立、客观和公正的原则，严格遵循有关法律、法规和市值评估理论，并尽专业上应有之注意，且对下列评估事项进行声明：</p>
                <p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">      1、本报告所使用之评估标的的预测信息，为评估委任人所提供，委托人需提供必要的资料并保证所提供资料的真实性、合法性、完整性，恰当使用评估报告是委托方和相关当事方的责任。</p>
                <p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">      2、评估报告中的分析、判断和结论受评估报告中假设和限定条件的限制，评估报告使用者应当充分考虑评估报告中载明的假设，限定条件、特别事项说明及其对评估结论的影响。</p>
                <p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">3、本系统评估目的是对评估对象进行成长性评价和智能估值，对评估对象法律权属确认或发表意见超出评估团队的评估范围。本评估报告不对评估对象的法律权属提供任何保证。</p>
                <p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">4、此报告仅供委托人内部管理决策参考，评估团队对委托人之任何筹资决策不负任何法律责任。</p>
                <p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">5、本报告内容非经委托人书面同意，不得进行复印或以任何方式将内容传递于第三人。本报告及结论，仅能基于管理目的使用，不得移做其他目的使用。</p>
                <p style="text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">6、本评估系统主要采用大数据智能化进行科技型中小企业估值，如需更精准的估值服务，建议咨询我们的线下服务。</p>';
        
                $pdf->writeHTML($html, true, false, true, false, '');

                $pdf->AddPage(); 
        
                $html = '
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">一 评估目的</h4>

               
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">本报告系委托人拟了解'.$companyInfo["name"].'的成长能力以及未来持续运营能力，特委托本公司对'.$companyInfo["name"].'的股权市值进行整体评估，确定被评估标的的总体价值，为'.$companyInfo["name"].'的融资、并购以及上市等资本市场行为提供参考依据。</p>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">二 评估基准日</h4>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">本次评估所采用的数据评估基准日为'.$summaryReport["base_date"].'。</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">评估基准日的确定对评估结果的影响符合常规情况，无特别影响因素。本次评估的取价标准为评估基准日有效的价格标准。</p>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">三 价值定义</h4>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">根据《国际评估准则》(IVS)，价值类型分为市场价值类型和市场价值以外的其他价值类型，评估的价值类型实际上是评估行为所基于的一系列可能存在的各种明显的或隐含的假设及前提，这些假设及前提往往决定了评估方法的选择和运用以及对评估结果的正确理解。根据本次评估的目的和评估对象，我们选用的价值类型为市场价值。</p>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">四 评估方法</h4>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">该智能市值评估是采用人工智能、机器学习、统计学和数据库的交叉方法，整合总体、产业和企业经济数据及文字，汇入BD模型（即Big-Data）,筛选出判定系数R-Square最高的市值预测模型来进行企业估值。</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">该系统采用了国际比较通用的企业评价以及大数据市值评估技术，包括多元回归，时间序列(ARIMA模型，EVA)，误差修正模型（ECM），VAR风险测量模型，矩阵分析，多目标决策法（AHP），隶属函数评估法，加权平均法，功效系数法，空间计分法。与此同时， 纳入中国资产评估协会《资产评估准则-企业价值》中介绍的收益法和市场法，考虑到科技型企业的高科技、高风险、高成长性，对传统的市场法和收益法两大估值方法进行了调整优化，然后运用社会统计软件，如SPSS，Stata，R，Maltab等来实现该智能市值评估系统。</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">无论是采用统计学预测模型还是选取调整优化的市场法和收益法模型来估值，通常要针对被评估企业的内在特征进行调整，以保证估值的准确性和合理性。</p>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">五 评估数据范围</h4>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">本系统评价参考数据主要是截止到2018年6月1日上交所、深交所等A股上市公司以及新三板挂牌公司，剔除了ST和财务信息不健全的公司数据。</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">数据来源主要为Datasteam、Wind等数据库,以及国家统计局、巨潮资讯网等。</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">数据内容：</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">1.宏观数据：GDP增长率，通货膨胀率，国债利率等；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">2.行业数据：包括行业经济增长率、行业市盈率、行业市净率、盈利能力指标、偿债能力指标、营运能力指标、成长能力指标等；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">3.企业数据：包含产品、经营策略、市场情况、科技成果、规范性等企业基本信息，净利润、营业收入、资产规模、股权结构和财务比率等财务数据，以及股票每日交易量和收盘价等数据。</p>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '
                <br>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">六 评估基准及假设条件</h4>

                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">（一） 评估基准</h4>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">对'.$companyInfo["name"].'的市值评估是根据公司以前年度实际的生产经营基础和潜力以及公司的经营业绩及各项经济指标，考虑公司持续经营的假设前提，并遵循国家现行法律、法规的有关规定，本着客观求实的原则，选取适当的模型进行预测评估。</p>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">（二） 预测的假设条件</h4>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">1 一般假设</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">①针对评估基准日时资产的实际状况，假设'.$companyInfo["name"].'能持续经营；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">②假设'.$companyInfo["name"].'的经营者是负责的，且公司管理层有能力担当其职务；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">③除非另有说明，假设'.$companyInfo["name"].'完全遵守所有有关的法律和法规；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">④假设委托人提供的历年财务资料所采取的会计政策和编写此份报告时所采用的会计政策在重要方面基本一致。</p>

                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">2特殊假设</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">①'.$companyInfo["name"].'所在地及中国的社会经济环境不产生大的变更，所遵循的国家现行法律、法规、制度及社会政治和经济政策与现时无重大变化；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">②假设在现有的运作方式和管理水平的基础上，将保持持续性经营，并在经营范围、方式上与现时方向保持一致；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">③假设'.$companyInfo["name"].'被收购股权或出让股权融资后后，其资产使用效率得到有效发挥；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">④有关信贷利率、汇率、赋税基准及税率，政策性征收费用等不发生重大变化；</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">⑤产成品或服务的社会需求将保持一定幅度增长;</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">⑥假设折现年限内将不会遇到重大的销售货款回收方面的问题（即坏账情况）;</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">⑦无其他人力不可抗拒因素及不可预见因素对企业造成重大不利影响。</p>
                <p style="line-height:150%; text-indent:2em; font-size: 12pt; text-align:justify; text-justify:inter-ideograph; font-family:楷体_GB2312;">评估系统根据资产评估的要求，认定这些前提条件在评估基准日时成立，当未来经济环境发生较大变化时，本评估团队将不承担由于前提条件的改变而推导出不同评估结果的责任。
                </p>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '
                <br>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">七 企业信息</h4>
                <h4 style="font-size: 14pt; font-family:楷体_GB2312;">（一）基础信息</h4>
        
                <table style="font-family:楷体_GB2312;">
                    <tbody>
                        <tr>
                            <td style="display:inline">企业名称：'.$companyInfo["name"].'</td>
                        <tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                            <br>
                            <td>注册地址：'.$companyInfo["province"].$companyInfo["city"].$companyInfo["area"].'</td>';

                            $pdf->writeHTML($html, true, false, true, false, '');
                            $html = '
                            <td>注册资本：'.$companyInfo["capital"].'万</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>成立日期：'.$companyInfo["establish_year"].'年</td>';

                            $pdf->writeHTML($html, true, false, true, false, '');
                            $html = '
                            <td>员工人数：'.$companyInfo["employees"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>所属行业：'.$companyInfo["hangye"].'</td>';

                            $pdf->writeHTML($html, true, false, true, false, '');
                            $html = '
                            <td>所处阶段：'.$companyInfo["jieduan"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');

                        $html = '
                        <tr>
                            <td>公司简介：'.$companyInfo["introduce"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');

                        $html = '
                        <tr>
                            <td>经营范围：'.$companyInfo["main_business"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>主要产品：'.$companyInfo["products"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>产品市场分布：'.$companyInfo["business_area"].'</td>
                        </tr>
                    </tbody>
                </table>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '<h4>（二）科技创新</h4>
                <table style="line-height:28px;">
                    <tbody>
                        <tr>
                            <td>科研人员占比：'.$companyInfo["kyryzb"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>知识产权：共'.$companyInfo["zscq"].'项，其中发明专利：'.$companyInfo["patent"].'项，实用新型：'.$companyInfo["shiyong"].'项，外观设计：'.$companyInfo["design"].'项</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>产品级别：'.$companyInfo["cpjb"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>产品是否被列入：'.$companyInfo["cpsfblr"].'</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>是否有正在研发的产品或技术：'.$companyInfo["zzyf"].'</td>
                        </tr>
                    </tbody>
                </table>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '
                <h3>（三）财务状况</h3>
                <table>
                    <tbody>
                        <tr>
                            <td>资产：'.$financialData["assets"].'万</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>负债：'.$financialData["liabilities"].'万</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>主营业收入：'.$financialData["main_operating_income"].'万</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>应收账款：'.$financialData["accounts_receivable"].'万</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>税后净利润：'.$financialData["net_profit"].'万</td>
                        </tr>';

                        $pdf->writeHTML($html, true, false, true, false, '');
                        $html = '
                        <tr>
                            <td>未来一年净利润预测：'.$financialForecast["forecast1"].'万</td>
                        </tr>
                    </tbody>
                </table>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '
                <br>
                <h3>八 成长性评价</h3>
                <p style="text-indent:2em; line-height:28px; padding-left:40px; padding-right:40px">采用系统构建的评估模型，结合委托人提供的数据并结合同行业数据信息，采用百分制打分方法，分数平均化后，得出'.$companyInfo["name"].'成长性总得分为'.sprintf("%.2f", $companyScore["growth"]).'分（详细数据见附表1）。</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '
                <p style="text-indent:2em; line-height:28px; padding-left:40px; padding-right:40px">按照智能评估系统得分等级可知，'.$companyInfo["name"].'属于'.$starReview["review"].'。其投资价值主要表现在'.$starReview["investment_value"].'。</p>';

                $pdf->writeHTML($html, true, false, true, false, '');// $lowZhibiao
                $html = '
                <p style="text-indent:2em; line-height:28px; padding-left:40px; padding-right:40px">但结合更加详细的二级指标分析可以看出，'.$factorName[$lowZhibiao[0]].'方面得分最低，同时'.$factorName[$lowZhibiao[1]].'、'.$factorName[$lowZhibiao[2]].'、'.$factorName[$lowZhibiao[3]].'得分较低。结合更加详细的三级指标可以看出企业的问题所在。</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '<p>'.$factorName[$lowYinzi[0]].'：'.$factorReview[$lowYinzi[0]].'</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '<p>'.$factorName[$lowYinzi[1]].'：'.$factorReview[$lowYinzi[1]].'</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '<p>'.$factorName[$lowYinzi[2]].'：'.$factorReview[$lowYinzi[2]].'</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '<p>'.$factorName[$lowYinzi[3]].'：'.$factorReview[$lowYinzi[3]].'</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '<p>'.$factorName[$lowYinzi[4]].'：'.$factorReview[$lowYinzi[4]].'</p>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $html = '<p>'.$factorName[$lowYinzi[5]].'：'.$factorReview[$lowYinzi[5]].'</p>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '
                <h4>九 评估结果</h4>
                <p style="text-indent:2em; line-height:28px; padding-left:40px; padding-right:40px">根据委托人所提供的'.$companyInfo["name"].'基本信息，以及结合A股上市公司，新三板公司行业信息，智能市值评估系统的分析结果如下（详细见表2）：</p>
                <p style="text-indent:2em; line-height:28px; padding-left:40px; padding-right:40px">'.$companyInfo["name"].'评估基准日的股权市值为：'.number_format($companyScore["shizhi"]).'元，大写人民币：'.$RMB.'。</p>

                <h4>十 评估建议</h4>
                <p style="text-indent:2em; line-height:28px; padding-left:40px; padding-right:40px">'.$evaluationTypeReview["review"].'</p>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->AddPage(); 
                $html = '
                <p>附表1：</p>
                <h4 align="center">'.$companyInfo["name"].'</h4>
                <h4 align="center">成长性评价报告</h4>
                <span style="margin:0px; padding:0px;" >报告输出日期：'.date("Y/m/d H:i:s", time()).'</span><br>
                <table style="width: 100%; font-size: 12pt; border-collapse: collapse; margin:auto; font-family:楷体_GB2312; text-align:center;">
                    <tbody>
                        <tr>
                            <td rowspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black;">目标层</td>
                            <td colspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black;">维度层</td>                    
                            <td colspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black;">指标层</td>
                            <td colspan="2" style="border-top: solid 1px black; padding: 0em .5em; border-bottom: solid 1px black;">因子层</td>                   
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">评价指标</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">得分</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">评价指标</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">得分</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">评价指标</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">得分</td>
                        </tr>
                        <tr>
                            <td rowspan="43" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor_all"]).'</td>
                            <td rowspan="10" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["dime1"].'</td>
                            <td rowspan="10" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime1"]).'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target1"].'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target1"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor1"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor1"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor2"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor2"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target2"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target2"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor3"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor3"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor4"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor4"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor5"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor5"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target3"].'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target3"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor6"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor6"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor7"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor7"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target4"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target4"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor8"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor8"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor9"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor9"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor10"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor10"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="11" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["dime2"].'</td>
                            <td rowspan="11" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime2"]).'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target5"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target5"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor11"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor11"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;  text-align:left;">'.$factorName["factor12"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor12"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;  text-align:left;">'.$factorName["factor13"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor13"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target6"].'</td>
                            <td rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target6"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor14"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor14"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor15"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor15"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor16"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor16"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor17"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor17"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target7"].'</td>
                            <td rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target7"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor18"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor18"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor19"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor19"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor20"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor20"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor21"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor21"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="8" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["dime3"].'</td>
                            <td rowspan="8" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime3"]).'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target8"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target8"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor22"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor22"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor23"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor23"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor24"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor24"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target9"].'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target9"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor25"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor25"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor26"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor26"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target10"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target10"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor27"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor27"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor28"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor28"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor29"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor29"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="6" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["dime4"].'</td>
                            <td rowspan="6" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime4"]).'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target11"].'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target11"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor30"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor30"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor31"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor31"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target12"].'</td>
                            <td rowspan="4" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target12"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor32"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor32"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor33"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor33"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor34"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor34"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor35"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor35"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="8" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["dime5"].'</td>
                            <td rowspan="8" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["dime5"]).'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target13"].'</td>
                            <td rowspan="2" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target13"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor36"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor36"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor37"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor37"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target14"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target14"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor38"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor38"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor39"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor39"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor40"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor40"]).'</td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["target15"].'</td>
                            <td rowspan="3" style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["target15"]).'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor41"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor41"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor42"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor42"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black; text-align:left;">'.$factorName["factor43"].'</td>
                            <td style="padding: 0em .5em; border-bottom: solid 1px black;">'.sprintf("%.2f", $factorScore["factor43"]).'</td>
                        </tr>
                    </tbody>
                </table>';


                $pdf->writeHTML($html, true, false, true, false, '');

                $pdf->AddPage(); 
                $html = '
                <span>附表2：</span>
                <h4 align="center">'.$companyInfo["name"].'</h4>
                <h4 align="center">大数据智能市值评估报告</h4>
                <span style="margin:0px; padding:0px;" display:inline>报告输出日期：'.date("Y/m/d H:i:s", time()).'</span>
                <h4 align="center">企业基础资料</h4>
                <table style="width: 100%; font-size: 12pt; border-collapse: collapse; font-family:楷体_GB2312; text-align:center;">
                    <tbody style="font-family:楷体_GB2312"> 
                        <tr>
                            <td style="border-top:1px solid black;"></td>
                            <td style="border-top:1px solid black;">净利润</td>         
                            <td style="border-top:1px solid black;">净资产</td>              
                            <td style="border-top:1px solid black;">股权数</td>
                        </tr>
                        <tr>
                            <td style="border-bottom:1px solid black;">期初</td>
                            <td style="border-bottom:1px solid black;">'.number_format($evaluationForecast[0]["f"]).'元</td>
                            <td style="border-bottom:1px solid black;">'.number_format($evaluationForecast[0]["eq"]).'元</td>
                            <td style="border-bottom:1px solid black;">'.$evaluationForecast[0]["stock"].'股</td>
                        </tr>
                    </tbody>
                </table>';

                $pdf->writeHTML($html, true, false, true, false, '');

                $html = '
                <h4 align="center">市值及估值指标预测</h4>
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
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[0]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[0]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[0]["p/e"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[0]["p/b"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[0]["peg"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">2018E</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[1]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[1]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[1]["p/e"])).'</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[1]["p/b"])).'</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[1]["peg"])).'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">2019E</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[2]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[2]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[2]["p/e"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[2]["p/b"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[2]["peg"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">2020E</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[3]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[3]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[3]["p/e"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[3]["p/b"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[3]["peg"]).'</td>
                        </tr>
                        <tr>
                            <td style="padding: .5em .5em;">2021E</td>
                            <td style="padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[4]["p"])).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[4]["eps"]).'元</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[4]["p/e"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[4]["p/b"]).'</td>
                            <td style="padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[4]["peg"]).'</td>
                        </tr>
                        <tr>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">2022E</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.number_format(sprintf("%.2f", $evaluationForecast[5]["p"])).'元</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[5]["eps"]).'元</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[5]["p/e"]).'</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[5]["p/b"]).'</td>
                            <td style="border-bottom:1px solid black; padding: .5em .5em;">'.sprintf("%.2f", $evaluationForecast[5]["peg"]).'</td>
                        </tr>
                    </tbody>
                </table>';

                $pdf->writeHTML($html, true, false, true, false, '');
                
                $html = '
                <p>注：</p>
                <p>1、'.$forecastNote[0].'</p>
                <p>2、'.$forecastNote[1].'</p>
                <p>3、'.$forecastNote[2].'</p>
            </div>

            </body>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        //输出PDF 
        $pdf->Output('t.pdf', 'I');  
     
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
    function rmb_format($money = 0, $int_unit = '元', $is_round = true, $is_extra_zero = false) {
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
    return $res;
}
   

}
?>
