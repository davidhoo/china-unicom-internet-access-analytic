<?php
ini_set('auto_detect_line_endings',TRUE);
error_reporting(0);

$trafficOffset = 6;
$ipOffset = 16;
$uriOffset = 32;
$uaOffset = 13;

if ($argc <= 1) {
    help();
}

if ($argc > 1) {
    $trafficOffset = $argv[2] ?? 6;
    $ipOffset = $argv[3] ?? 16;
    $uriOffset = $argv[4] ?? 21;
    $uaOffset = $argv[5] ?? 23;
}

$file = $argv[1];

if (!file_exists($file)) {
    echo 'File not found!';
    help();
}

function help() {
    echo "\n";
    echo "Usage:\n";
    echo "\t php analytic.php filename.csv\n";
    exit;
}
$handle = fopen($file, "r");

$domainWhiteList = [
    "weibo.com",
    "weibo.cn",
    "sina.com.cn",
    "sina.cn",
    "sinaimg.cn",
    "sinaimg.com",
    "sinajs.cn",
    "sina.cn",
    "miaopai.com",
    "yzbo.tv",
    "xiaoka.tv",
    "xiaokaxiu.com",
    "yizhibo.com",
    "kandian.com",
    "kandian.cn",
];

$ipWhiteList = [
    '101.71.53.126',
    '27.221.16.253',
    '119.188.72.124',
    '218.9.147.249',
    '61.158.251.243',
    '221.204.241.189',
    '112.90.6.247',
    '123.125.105.246',
    '123.125.105.231',
    '202.108.7.133',
    '202.108.33.119',
    '123.125.106.66',
    '123.125.106.67',
    '123.126.42.243',
    '123.125.29.171',
    '180.149.134.230',
    '58.205.212.101',
    '111.13.87.210',
    '36.51.254.239',
    '183.60.92.208',
    '183.60.92.209',
    '112.90.6.249',
    '112.90.6.250',
    '183.232.24.11',
    '183.232.24.228',
    '114.134.80.171',
    '114.134.80.172',
    '60.205.83.166',
    '123.125.29.166',
    '112.90.6.227',
    '119.188.72.60',
    '119.188.72.61',
    '111.161.68.169',
    '111.161.68.170',
    '112.90.6.59',
    '112.90.6.60',
    '27.221.16.66',
    '27.221.16.67',
    '123.126.157.205',
    '123.126.157.206',
    '124.95.163.166',
    '124.95.163.167',
    '27.221.16.100',
    '27.221.16.101',
    '125.211.213.119',
    '125.211.213.120',
    '221.204.241.165',
    '221.204.241.166',
    '61.158.251.200',
    '61.158.251.201',
    '112.90.152.10',
    '112.90.152.11',
    '125.39.59.72',
    '125.39.59.73',
    '121.22.4.51',
    '121.22.4.52',
    '101.71.100.123',
    '101.71.100.21',
    '101.71.100.22',
    '101.71.100.23',
    '101.71.100.24',
    '101.71.100.25',
    '101.71.100.26',
    '101.71.100.27',
    '101.71.100.76',
    '101.71.100.77',
    '101.71.100.78',
    '61.135.153.45',
    '61.135.153.46',
    '61.135.153.47',
    '123.125.29.127',
    '123.125.29.203',
    '123.125.29.236',
    '180.149.134.252',
    '123.125.29.133',
    '123.126.42.42',
    '123.126.42.248',
    '111.13.89.71',
    '172.16.89.97',
    '123.126.42.41',
    '123.126.42.43',
    '123.125.104.143',
    '123.125.104.183',
    '123.125.104.39',
    '123.125.104.41',
    '123.125.104.40',
    '123.126.42.41',
    '123.125.106.168',
    '123.125.104.39',
    '101.201.199.53',
    '101.201.154.24',
    '101.201.101.64',
    '101.201.141.203',
    '101.201.74.239',
    '218.11.0.14',
    '180.97.162.239',
    '120.55.238.132',
    '60.205.236.179',
    '182.92.26.214',
    '60.205.91.94',
    '60.205.9.218',
    '101.200.30.14',
    '139.214.193.113',
    '122.138.54.72',
    '175.21.164.98',
    '60.220.194.204',
    '110.242.16.120',
    '121.18.168.148',
    '60.13.41.70',
    '113.200.235.32',
    '103.251.162.2',
    '115.63.103.171',
    '61.54.84.166',
    '218.29.198.20',
    '36.250.240.94',
    '36.250.76.195',
    '36.250.227.37',
    '113.207.72.9',
    '113.207.72.96',
    '113.207.4.107',
    '101.71.78.234',
    '101.71.89.200',
    '113.200.235.189',
    '61.158.229.73',
    '61.179.228.67',
    '101.66.227.56',
    '58.20.197.16',
    '122.13.197.227',
    '60.220.194.26',
    '153.35.175.18',
    '111.161.120.23',
    '61.163.117.44',
    '218.60.91.16',
    '175.154.186.30',
];

function inWhiteList($line) {
    global $domainWhiteList, $ipWhiteList, $trafficOffset, $ipOffset, $uriOffset;
    $pattern = '/.*' . implode('|.*', $domainWhiteList) . '/';
    if(preg_match($pattern, explode(' ', trim($line[$uriOffset]))[0]) || in_array($line[$ipOffset], $ipWhiteList)) {
        return true;
    }
    return false;
}

while($line = fgetcsv($handle, 2048, ",")) {
    if (in_array('总流量(kb)', $line)) {
        $descIndex = array_flip($line);
        $trafficOffset = $descIndex['总流量(kb)'];
        $ipOffset = $descIndex['访问IP'];
        $uriOffset = $descIndex['访问网址'];
        $uaOffset = $descIndex['User Agent'];
    }
    $total += $line[$trafficOffset];
    if (empty($line[$trafficOffset]) || empty($line[$ipOffset])) {
        continue;
    }

    $desc[$line[$ipOffset]][0][] = $line[$uriOffset];
    $desc[$line[$ipOffset]][1][] = $line[$uaOffset];

    if (inWhiteList($line)) {
        continue;
    }
    $idx ++;
    $sum += $line[$trafficOffset];
    /*
    echo $idx, "\t",
        str_pad($line[$trafficOffset],10, ' ', STR_PAD_LEFT), "\t",
        str_Pad($line[$ipOffset], 15, ' '), "\t",
        str_pad($line[$uriOffset], 40, ' '), "\t",
        $line[$uaOffset], "\t",
        "\n";
     */

    // top 10
    $data[$line[$ipOffset]] += $line[$trafficOffset];
}

echo str_pad(' Stat ', 150, '=', STR_PAD_BOTH), "\n",
    "sum:", round($sum/1024, 2), "MB\t", "total:", round($total/1024, 2), "Mb\tpercent:", round($sum*100/$total,2), "%\n";

arsort($data);

echo str_pad(' Top 10 ', 150, '=', STR_PAD_BOTH), "\n";
$data = array_slice($data,0, 9);
foreach($data as $k=>$v) {
    echo $k, "\t" , str_pad($v, 10, ' ', STR_PAD_LEFT), "KB\t",
        str_pad(round($v*100/$sum),3, ' ', STR_PAD_LEFT), "%\t",
        str_pad(join(',', array_unique($desc[$k][1])), 50), "\t",
        str_pad(join(',', array_slice(array_unique($desc[$k][0]), 0, 3)),30), "\n";
}

ini_set('auto_detect_line_endings',FALSE);
