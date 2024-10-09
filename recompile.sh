pwd
sleep 0.5
echo "Enabling maintenance mode......"
php bin/magento maintenance:enable
sleep 0.5
echo "Cleaning and flushing Cache......."
php -d memory_limit=-1 bin/magento cache:clean
php -d memory_limit=-1 bin/magento cache:flush
echo "Cache cleared"
sleep 0.5
echo "Removing var/cache/......."
rm -R var/cache/*
echo "Removed /var/cache"
sleep 0.5
echo "Removing var/page_cache/......."
rm -R var/page_cache/*
echo "Removed page_cache"
sleep 0.5
echo "Removing var/session/......."
rm -R var/session/*
echo "Removed Session"
sleep 0.5
echo "Removing generated/......."
rm -R generated/code/*
rm -R generated/metadata/*
echo "Removed generated"
sleep 0.5
echo "Removing pub/static/frontend......."
rm -R pub/static/frontend/*
echo "Removed pub/static/frontend"
sleep 0.5
echo "Removing pub/static/adminhtml......."
rm -R pub/static/adminhtml/*
echo "Removed pub/static/frontend"
sleep 0.5
echo "Removing var/view_preprocessed......."
rm -R var/view_preprocessed/*
echo "Removed var/view_preprocessed"
sleep 0.5
echo "Running upgrade command......."
php -d memory_limit=-1 bin/magento setup:upgrade
echo "Upgrade command completed"
sleep 0.5
echo "Running di compile command......."
php -d memory_limit=-1 bin/magento setup:di:compile
echo "di compile completed"
sleep 0.5
echo "Node Pakages Install"
~/nodevenv/node20/20/bin/npm --prefix="app/design/frontend/WeyLighting/Custom/web/tailwind" ci
sleep 0.5
echo "purging css....."
~/nodevenv/node20/20/bin/npm --prefix="app/design/frontend/WeyLighting/Custom/web/tailwind" run build
sleep 0.5
echo "Running Static content deploy command......."
php -d memory_limit=-1 bin/magento setup:static-content:deploy -f
echo "Static content deploy completed"
sleep 0.5
echo "Permissions command completed"
sleep 0.5
echo "Indexer Reindex..."
php bin/magento indexer:reindex
sleep 0.5
echo "Disabling maintenance mode......"
php bin/magento maintenance:disable
sleep 0.5
echo "All Done :)"
