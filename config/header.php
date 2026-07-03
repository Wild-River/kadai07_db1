    <header class="site-header">
        <div class="site-header__inner">
            <h1 class="site-title"><a href="index.php">コーヒー生豆在庫管理</a></h1>
            <button type="button" class="menu-toggle" aria-label="メニューを開く" aria-expanded="false" aria-controls="site-nav">
                <span class="menu-toggle__bar"></span>
                <span class="menu-toggle__bar"></span>
                <span class="menu-toggle__bar"></span>
            </button>
            <nav class="site-nav" id="site-nav">
                <div class="nav-group">
                    <a href="index.php">在庫管理</a>
                    <button type="button" class="nav-toggle" aria-label="サブメニューを開く" aria-expanded="false">▾</button>
                    <div class="nav-dropdown">
                        <a href="bean_create.php">生豆登録</a>
                    </div>
                </div>
                <a href="movement_create.php">入出荷記録</a>
                <div class="nav-group">
                    <a href="customer_list.php">顧客管理</a>
                    <button type="button" class="nav-toggle" aria-label="サブメニューを開く" aria-expanded="false">▾</button>
                    <div class="nav-dropdown">
                        <a href="customer_create.php">顧客登録</a>
                    </div>
                </div>
                <a href="logout.php" title="ログアウト" aria-label="ログアウト">ログアウト</a>
            </nav>
        </div>
    </header>
    <script src="js/script.js"></script>