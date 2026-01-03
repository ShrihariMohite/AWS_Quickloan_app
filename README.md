# AWS_Quickloan_app
Quickloan application using AWS services like (EC2,RDS,load balancer, Auto scaling group)

Finance Application - Loan Management System
A comprehensive two-tier web application for managing loan applications with automated deployment, load balancing, and auto-scaling capabilities on AWS.
📋 Project Overview
This project implements a finance/loan application system with:

Frontend: Nginx web server serving the application interface
Backend: PHP-based application logic with MySQL database
Infrastructure: AWS EC2 with RDS, Elastic Load Balancing, and Auto Scaling
Deployment: Automated setup using WinSCP and shell scripts

🏗️ Architecture
┌─────────────────┐
│   Internet      │
└────────┬────────┘
         │
    ┌────▼─────┐
    │   ALB    │
    └────┬─────┘
         │
    ┌────▼──────────────────┐
    │   Target Group        │
    │   (Health Checks)     │
    └────┬──────────────────┘
         │
    ┌────▼────────────┐
    │  Auto Scaling   │
    │     Group       │
    └────┬────────────┘
         │
    ┌────▼─────────────────────┐
    │   EC2 Instances          │
    │   (Nginx + PHP-FPM)      │
    └────┬─────────────────────┘
         │
    ┌────▼────────┐
    │  RDS MySQL  │
    │  (Database) │
    └─────────────┘
🚀 Features

Load Balancing: Distributes traffic across multiple EC2 instances
Auto Scaling: Automatically adjusts capacity based on demand

Target value: 50% CPU utilization
Health check grace period: 90 seconds
Warmup time: 60 seconds


High Availability: Multi-AZ deployment with health monitoring
Security: SSH key authentication, security groups, private database access
Monitoring: CloudWatch integration for performance metrics

🛠️ Technology Stack
Frontend

Nginx: Web server and reverse proxy
HTML/CSS/JavaScript: User interface

Backend

PHP 8.2: Application logic
PHP-FPM: FastCGI Process Manager
MySQL: Database management
MariaDB: Database client tools

Infrastructure

AWS EC2: Compute instances (Amazon Linux)
AWS RDS: Managed MySQL database
Elastic Load Balancer: Application Load Balancer
Auto Scaling Groups: Dynamic capacity management
Launch Templates: Instance configuration templates

📦 Prerequisites

AWS Account with appropriate permissions
Key pair for SSH access
Domain name or public IP for access
WinSCP (for file transfer)
Basic knowledge of Linux commands

🔧 Installation
Step 1: Database Setup (RDS)

Create RDS MySQL instance:

Engine: MySQL
Instance class: Choose based on requirements
Database name: quickloan-db
Username: Set your credentials
Password: Set secure password
Public access: No (sensitive information)
Security group: Configure for port 3306


Create database schema:

sqlCREATE DATABASE quickloan-db;
USE quickloan-db;
CREATE TABLE applications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    loan_type VARCHAR(50) NOT NULL
);
Step 2: EC2 Instance Configuration

Launch Sample Server Instance:

AMI: Amazon Linux 2
Instance type: t2.small (or as needed)
Key pair: Create/select existing
Security groups:

Port 22 (SSH) - Everyone
Port 80 (HTTP) - Everyone
Port 3306 (MySQL) - From RDS security group




Install Required Packages:

bash# Update system
sudo dnf update -y

# Install Nginx
sudo dnf install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx

# Install PHP 8.2 and extensions
sudo dnf install php-fpm php-mysqlnd php-pdo php-mbstring -y

# Install MySQL client
sudo yum install mariadb -y

Configure Nginx:

bash# Create configuration
sudo nano /etc/nginx/conf.d/quickloan.conf
Add configuration:
nginxserver {
    listen 80;
    server_name your-domain.com;  # or public IP
    root /usr/share/nginx/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

Configure PHP-FPM:

bashsudo systemctl start php-fpm
sudo systemctl enable php-fpm
sudo systemctl restart nginx

Set Permissions:

bashsudo chown -R nginx:nginx /usr/share/nginx/html/
sudo chmod -R 755 /usr/share/nginx/html/
Step 3: Deploy Application

Transfer Files via WinSCP:

Protocol: SCP
Host: EC2 public IP
Port: 22
Username: ec2-user
Authentication: Use private key file


Copy Application Files:

Navigate to: /home/ec2-user/
Upload your project files (HTML, PHP, CSS, JS)
Includes: nginx/, public/ folders


Move Files to Web Root:

bashsudo mv /home/ec2-user/nginx/html/* /usr/share/nginx/html/
sudo mv /home/ec2-user/public/* /usr/share/nginx/html/
Step 4: Database Connection

Update Connection Settings:
Edit db-connection.php:

php<?php
$servername = "your-rds-endpoint.rds.amazonaws.com";
$username = "your_username";
$password = "your_password";
$dbname = "quickloan-db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

Test Database Connection:

bashmysql -h your-rds-endpoint.rds.amazonaws.com -P 3306 -u username -p
# Enter password
# Test connection
USE quickloan-db;
SHOW TABLES;
Step 5: Create AMI and Launch Template

Create AMI from Sample Server:

Right-click instance → Create Image
Name: quickloan-ami
Description: "Frontend of quickloan application"
Don't delete AMI or snapshot


Create Launch Template:

Name: quickloan-template
AMI: Select created AMI
Instance type: t2.small
Key pair: Your existing key
Security group: Same as sample server
Configuration description details about server



Step 6: Configure Load Balancer

Create Target Group:

Type: Instance
Protocol: HTTP, Port: 80
Health check path: / (or specific health endpoint)
Health check interval: 30 seconds
Healthy threshold: 2
Unhealthy threshold: 2
Timeout: 5 seconds
Advanced: Enable stickiness if needed


Create Application Load Balancer:

Name: loan-app-lb
Scheme: Internet-facing
IP address type: IPv4
Availability zones: Select 2+ zones in different AZs
Security group: Port 80, 443 - Everyone
Target group: Select created target group


Register Sample Server (temporary for testing):

Go to Target Group → Targets
Register sample EC2 instance
Wait for health check to pass



Step 7: Configure Auto Scaling

Create Auto Scaling Group:

Name: loan-app-asg
Launch template: quickloan-template
Version: Latest
Network: Select VPC and multiple subnets (2+ AZs)


Configure Scaling Settings:

Desired capacity: 2
Minimum capacity: 1
Maximum capacity: 4
Health check type: ELB
Health check grace period: 90 seconds


Add Target Tracking Scaling Policy:

Policy name: target-tracking-policy
Metric type: Average CPU Utilization
Target value: 50%
Instance warmup: 60 seconds
Default instance warmup: 60 seconds
Enable: create autoscaling group


Attach Load Balancer:

Select target group created earlier
Auto Scaling will automatically register instances



Step 8: Testing

Access Application:

URL: http://your-load-balancer-dns-name
Test form submission
Verify data in database


Verify Auto Scaling:

Monitor CloudWatch metrics
Test by creating CPU load
Watch instances scale up/down


Check Database Entries:

bashmysql -h endpoint -P 3306 -u username -p
USE quickloan-db;
SELECT * FROM applications;

📝 Configuration Files
Database Connection (db-connection.php)
php$servername = "your-rds-endpoint";
$username = "admin";
$password = "your_password";
$dbname = "quickloan-db";
Nginx Configuration (/etc/nginx/conf.d/quickloan.conf)

Server block listening on port 80
PHP-FPM integration via unix socket
Document root: /usr/share/nginx/html

🔒 Security Considerations

Database Security:

RDS not publicly accessible
Strong password requirements
Security group restricts access to EC2 instances only


Application Security:

SSH key-based authentication
Security groups with minimal required ports
Regular security updates


Network Security:

Load balancer in public subnets
Application servers can be in private subnets
RDS in private subnet



📊 Monitoring

CloudWatch Metrics: CPU, memory, network
Load Balancer Metrics: Request count, latency, target health
Auto Scaling Activity: Scale-up/down events
RDS Metrics: Database connections, query performance

🐛 Troubleshooting
Common Issues

502 Bad Gateway:

Check PHP-FPM status: sudo systemctl status php-fpm
Verify Nginx configuration
Check file permissions


Database Connection Failed:

Verify RDS endpoint
Check security group rules
Test connection with MySQL client


Auto Scaling Not Working:

Check CloudWatch alarms
Verify health check settings
Review instance launch logs


Health Check Failing:

Ensure application responds on health check path
Check security group allows ALB traffic
Verify target port configuration



📚 Additional Resources

AWS EC2 Documentation
Nginx Documentation
PHP-FPM Configuration
AWS Auto Scaling Guide

👥 Project Information

Project Type: Two-tier Finance Application
Deployment: AWS Cloud Infrastructure
Monitoring: Period capacity checks enabled
Health Checks: Every 90 seconds with 60-second warmup

📄 License
This project documentation is for educational purposes.
