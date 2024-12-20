<?php
ob_start();
include './cfg.php';
?>
<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Box - Neko</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
</head>
<body>
<style>
@media (max-width: 767px) {
    .row a {
        font-size: 9px; 
    }
}

.table-responsive {
    width: 100%;
}
</style>
<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-lg">🏠 首页</a>
        <a href="./mihomo_manager.php" class="col btn btn-lg">📂 Mihomo</a>
        <a href="./singbox_manager.php" class="col btn btn-lg">🗂️ Sing-box</a>
        <a href="./box.php" class="col btn btn-lg">💹 订阅转换</a>
        <a href="./filekit.php" class="col btn btn-lg">📦 文件助手</a>
<div class="outer-container">
    <div class="container">
        <h1 class="title text-center" style="margin-top: 3rem; margin-bottom: 2rem;">Sing-box 订阅转换模板</h1>
        <div class="alert alert-info">
            <h4 class="alert-heading">帮助信息</h4>
            <p>请选择一个模板以生成配置文件：根据订阅节点信息选择对应模板，否则启动不了。
            <ul>
                <li><strong>默认模板 1</strong>：无地区  无分组 通用。</li>
                <li><strong>默认模板 2</strong>：无地区  带分流规则 通用。</li>
                <li><strong>默认模板 3</strong>：香港 台湾 新加坡 日本 美国 韩国 分组 带分流规则。</li>
            </ul>
        </div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subscribeUrl" class="form-label">订阅链接地址:</label>
                <input type="text" class="form-control" id="subscribeUrl" name="subscribeUrl" required>
            </div>
            <div class="mb-3">
                <label for="customFileName" class="form-label">自定义文件名（无需输入后缀）</label>
                <input type="text" class="form-control" id="customFileName" name="customFileName" placeholder="输入自定义文件名">
            </div>
            <fieldset class="mb-3">
                <legend class="form-label">选择模板</legend>
                <div class="row">
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate1" name="defaultTemplate" value="1" checked>
                        <label class="form-check-label" for="useDefaultTemplate1">默认模板 1</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate2" name="defaultTemplate" value="2">
                        <label class="form-check-label" for="useDefaultTemplate2">默认模板 2</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate3" name="defaultTemplate" value="3">
                        <label class="form-check-label" for="useDefaultTemplate3">默认模板 3</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate4" name="defaultTemplate" value="4">
                        <label class="form-check-label" for="useDefaultTemplate3">默认模板 4</label>
                    </div>
                </div>
                <div class="mt-3">
                    <input type="radio" class="form-check-input" id="useCustomTemplate" name="templateOption" value="custom">
                    <label class="form-check-label" for="useCustomTemplate">使用自定义模板URL:</label>
                    <input type="text" class="form-control" id="customTemplateUrl" name="customTemplateUrl" placeholder="输入自定义模板URL">
                </div>
            </fieldset>
            <div class="mb-3">
                <button type="submit" name="generateConfig" class="btn btn-info">生成配置文件</button>
            </div>
        </form>

        <?php
        $dataFilePath = '/tmp/subscription_data.txt';
        $configFilePath = '/etc/neko/config/sing-box.json';
        $downloadedContent = ''; 

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateConfig'])) {
            $subscribeUrl = trim($_POST['subscribeUrl']);
            $customTemplateUrl = trim($_POST['customTemplateUrl']);
            $templateOption = $_POST['templateOption'] ?? 'default';
            $dataContent = "订阅链接地址: " . $subscribeUrl . "\n" . "自定义模板URL: " . $customTemplateUrl . "\n";
            file_put_contents($dataFilePath, $dataContent, FILE_APPEND);
            $subscribeUrlEncoded = urlencode($subscribeUrl);
            
            $customFileName = trim($_POST['customFileName']);
            if (empty($customFileName)) {
               $customFileName = 'sing-box';  
            }

            if (substr($customFileName, -5) !== '.json') {
                $customFileName .= '.json';
            }

            if ($templateOption === 'custom' && !empty($customTemplateUrl)) {
                $templateUrlEncoded = urlencode($customTemplateUrl);
            } else {
                $defaultTemplates = [
                    '1' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_7.json",
                    '2' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_6.json",
                    '3' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_8.json",
                    '4' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_9.json"
                ];

                $templateUrlEncoded = urlencode($defaultTemplates[$_POST['defaultTemplate']] ?? $defaultTemplates['mixed']);
            }

            $completeSubscribeUrl = "https://sing-box-subscribe-doraemon.vercel.app/config/{$subscribeUrlEncoded}&file={$templateUrlEncoded}";
            $tempFilePath = '/tmp/' . $customFileName;
            $command = "wget -O " . escapeshellarg($tempFilePath) . " " . escapeshellarg($completeSubscribeUrl);
            exec($command, $output, $returnVar);
            $logMessages = [];

            if ($returnVar !== 0) {
                $logMessages[] = "无法下载内容: " . htmlspecialchars($completeSubscribeUrl);
            } else {
                $downloadedContent = file_get_contents($tempFilePath);
                if ($downloadedContent === false) {
                    $logMessages[] = "无法读取下载的文件内容";
                } else {
                    $configFilePath = '/etc/neko/config/' . $customFileName; 
                    if (file_put_contents($configFilePath, $downloadedContent) === false) {
                        $logMessages[] = "无法保存修改后的内容到: " . $configFilePath;
                    } else {
                        $logMessages[] = "配置文件生成并保存成功: " . $configFilePath;
                        $logMessages[] = "生成并下载的订阅URL: <a href='" . htmlspecialchars($completeSubscribeUrl) . "' target='_blank'>" . htmlspecialchars($completeSubscribeUrl) . "</a>";
                    }
                }
            }

            echo "<div class='result-container'>";
            echo "<form method='post' action=''>";
            echo "<div class='mb-3'>";
            echo "<textarea id='configContent' name='configContent' class='form-control' style='height: 300px;'>" . htmlspecialchars($downloadedContent) . "</textarea>";
            echo "</div>";
            echo "<div class='text-center'>";
            echo "<button class='btn btn-info' type='button' onclick='copyToClipboard()'><i class='fas fa-copy'></i> 复制到剪贴</button>";
            echo "<input type='hidden' name='saveContent' value='1'>";
            echo "<button class='btn btn-success' type='submit'>保存修改</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
            echo "<div class='alert alert-info' style='word-wrap: break-word; overflow-wrap: break-word;'>";
            foreach ($logMessages as $message) {
            echo $message . "<br>";
            }
            echo "</div>";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['saveContent'])) {
            if (isset($_POST['configContent'])) {
                $editedContent = trim($_POST['configContent']);
                if (file_put_contents($configFilePath, $editedContent) === false) {
                    echo "<div class='alert alert-danger'>无法保存修改后的内容到: " . htmlspecialchars($configFilePath) . "</div>";
                } else {
                    echo "<div class='alert alert-success'>内容已成功保存到: " . htmlspecialchars($configFilePath) . "</div>";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clearData'])) {
            if (file_exists($dataFilePath)) {
                file_put_contents($dataFilePath, '');
                echo "<div class='alert alert-success'>保存的数据已清空。</div>";
            }
        }

        if (file_exists($dataFilePath)) {
            $savedData = file_get_contents($dataFilePath);
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h2 class='card-title'>保存的数据</h2>";
            echo "<pre>" . htmlspecialchars($savedData) . "</pre>";
            echo "<form method='post' action=''>";
            echo "<button class='btn btn-danger' type='submit' name='clearData'>清空数据</button>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>
</div>
<script src="./assets/bootstrap/jquery.min.js"></script>
<script>
    function copyToClipboard() {
        const copyText = document.getElementById("configContent");
        copyText.select();
        document.execCommand("copy");
        alert("已复制到剪贴板");
    }
</script>
</body>
</html>
