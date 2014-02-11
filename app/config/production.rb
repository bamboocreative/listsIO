server "lists.io", :app, :web, :db, :primary => true
set :deploy_to, "/var/www/vhosts/#{domain}"