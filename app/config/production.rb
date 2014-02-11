server "72.47.211.211", :app, :web, :db, :primary => true
set :deploy_to, "/var/www/vhosts/#{domain}"