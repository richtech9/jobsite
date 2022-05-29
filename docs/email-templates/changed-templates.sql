CREATE TABLE wp_email_templates
(
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  subject varchar(255) NOT NULL,
  content longtext NOT NULL,
  modified_date datetime NOT NULL
);
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (1, 'Account Activation', '{{activation_link}}', 'Account Activation - FreeLinguist', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Welcome to PeerOK! Click the link below to activate your account.
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                {{activation_link}}
            </span>
        </td>
    </tr>
    
    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    </tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (2, 'Forgot Password', '{{password}}', 'Reset Your Password', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            Your new password is: {{password}}.
        </td>
    </tr>

    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <a href=\\"{{login_url}}\\">Please click this link to login</a>
        </td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    </tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (6, 'Hire Translate', '{{job_path}}', 'Congratulations! You are hired!', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Congratulations! You are hired!
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\">Congratulations! You have been hired for job {{job_path}}. Please respond to the hiring as soon as possible.</span>
        </td>
    </tr>
    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    </tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (11, 'W9 Form', '', 'W9 Form', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Please fill and sign in your W-9 form using the following link.
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                <a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\"
                   href=\\"https://cudasign.com/s/WQvjviUo\\"
                   target=\\"_blank\\"
                >
                    W-9 form
                </a>
            </span>
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                After submission, the signed W-9 form will be emailed to you. Please upload the signed W-9 form to our website on the Wallet page.
                The form illustrations might be helpful to understand how to fill in the form.
                <br />
                <a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\"
                   href=\\"https://cudasign.com/s/WQvjviUo\\" target=\\"_blank\\"
                >
                    W-9 form illustrations
                </a>
            </span>
        </td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
</table>', '2016-01-17 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (12, 'W8BEN Form', '', 'W8BEN Form', '<table>
    <tbody>

    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            W8BEN Form
        </td>
    </tr>
    
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                Please fill and sign in your W8BEN form using the following link.
            </span>
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                <a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\" 
                   href=\\"https://cudasign.com/s/aMatUbjJ\\" 
                   target=\\"_blank\\"
                >
                    W-8BEN
                </a>
            </span>
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <p>
                <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                    After submission, the signed W-8BEN form will be emailed to you. Please upload the signed W-8BEN form to our website on the Wallet page.
                </span>
            </p>
            <p>
                The following illustrations might be helpful for you to fill in the form.<br />
                <a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\" 
                   href=\\"https://www.youtube.com/watch?v=K0Sy_cvWsG0\\" target=\\"_blank\\"
                >W-8BEN form illustrations.
                </a>
            </p>
        </td>
    </tr>
    

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    </tbody>
</table>
', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (13, 'Tax Form', '', 'Tax Form', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">Hello</td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\"><span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">Please find the attached form</span></td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    
    </tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (14, 'Bid Statement', ' {{job_title}},  {{job_path}}', 'Bid Statement', '<table>
    <tbody>

    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Bid Statement
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                A new bid on the job {{job_title}} is waiting for your approval.
            </span>
        </td>
    </tr>
    <tr>
        <td><span class=\\"\\">{{job_path}}</span></td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (16, 'Admin receve Withdraw Request', '{{user_email}}, {{withdrawl_amount}}, {{withdrawal_message}}', 'Withdraw Amount', '<table>
    <tbody>

    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">Withdraw Amount</td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <p>&nbsp;</p>
            <p>{{user_email}} sends a withdraw request for {{withdrawl_amount}}.</p>
            <p>&nbsp;</p>
            <p>{{withdrawal_message}}</p>
        </td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (17, 'Email Change', '{{activation_link}}', 'New acivation link to change email', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">Here is the link for you to change email:</td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">      {{activation_link}}</td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (21, 'Refill Account by admin', '{{refill_amount}},{refill_message}}', 'Refill account by PeerOK administrator', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            {{refill_amount}} USD has been refilled to your account by PeerOK.
        </td>
    </tr>
    
    <tr>
        <td>     Refill note from FreeLinguist: {{refill_message}}.</td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    
    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (30, 'Send Message to customer or linguist  by Evaluation Sub admin', '{{message}}', 'Send Message to customer or linguist  by Evaluation Sub admin', '<table>
    <tbody>
    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{header}}
        </td>
    </tr>
    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Send Message to customer or linguist by Evaluation Sub admin
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\"> </td>
    </tr>
    <tr>
        <td><span class=\\"\\">{{message}}</span></td>
    </tr>
    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>
    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (31, 'To Receive Notifications Of New Available Jobs(HOURLY)', '{{new_available_job}}', 'Posts Of New Jobs', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            New Jobs have been posted!
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                {{new_available_job}}
            </span>
        </td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>
', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (32, 'To Receive Notifications Of New Available Jobs(DAILY)', '{{new_available_job}}', 'New Jobs Posted', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>


    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            New Jobs have been posted!
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                {{new_available_job}}
            </span>
        </td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (33, 'Account Close', '', 'Account has been closed', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Your Account has been closed.
        </td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (34, 'Account Activated', '', 'Account Activated', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Account Activated
        </td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (35, 'PLACE NEW ORDER OR JOB', '{{job_link}},{{order_placed_date}}
', 'A new order has been placed ', '<table>
    <tbody>

    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Your order has been placed
        </td>
    </tr>

    <tr>
        <td>
            <span class=\\"\\">Order Placed : {{order_placed_date}}</span>
        </td>
    </tr>

    <tr>
        <td>
            <span class=\\"barcode-text\\">Items Ordered: {{job_link}}</span>
        </td>
    </tr>

    <tr>
        <td>
            <span class=\\"\\">Please log in to your account to check the details</span>
        </td>
    </tr>



    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
           {{footer}}
        </td>
    </tr>
    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (36, 'CANCEL ORDER OR JOB', '{{job_link}}', 'Order cancelled', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">Your order has been canceled.</td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\"> </td>
    </tr>
    <tr>
        <td><span class=\\"\\">{{job_link}}</span></td>
    </tr>

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>


', '2016-10-14 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (41, 'Email to linguist for bidding deposit is refunded when job cancel', '{{job_title}}', 'Bidding deposit is refunded', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 16px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Unfortunately, Job {{job_title}}  has been cancelled. Your bidding security deposit has been fully refunded.
        </td>
    </tr>
    

    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>


', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (42, 'Email to user Refill credit', '{{credit}} {{processing_fee}} {{total_amount}}', 'Credits have been refilled. ', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Credits have been refilled.
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                Your have refilled credits
            </span>
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                {{credit}} - {{processing_fee}} ={{total_amount}} USD.
            </span>
        </td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>




', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (43, 'Email to linguist when places bid on a job
', '{{job_title}}', 'You have placed a new bid', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>
    

    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            You have placed a new bid on the job
        </td>
    </tr>
    <tr>
        <td><span class=\\"\\">{{job_title}}</span></td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>
', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (54, 'AUTOMATIC JOB REJECTTION APPROVE EMAIL TO LINGUIST', '{{job_name}}', 'AUTOMATIC JOB REJECTION APPROVE EMAIL TO LINGUIST', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>



    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            AUTOMATIC JOB REJECTION APPROVE EMAIL TO LINGUIST
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                Since you haven\\''t replied timely, Job {{job_name}} has been rejected automatically.
                The security deposit has been forfeited following our Terms of Service
            </span>
        </td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>
', '2016-10-13 00:00:00');
INSERT INTO wp_email_templates (id, title, description, subject, content, modified_date) VALUES (55, 'AUTOMATIC JOB REJECTTION APPROVE EMAIL TO CUSTOMER', '{{job_name}}', 'AUTOMATIC JOB REJECTION APPROVE EMAIL TO CUSTOMER', '<table>
    <tbody>
    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>



    <tr style=\\"height: 57px;\\">
        <td class=\\"\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            AUTOMATIC JOB REJECTION APPROVE EMAIL TO CUSTOMER
        </td>
    </tr>
    <tr style=\\"height: 27px;\\">
        <td style=\\"height: 27px;\\">
            <span class=\\"\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular,serif;\\">
                 Job {{job_name}} has been successfully rejected and you have been fully refunded
            </span>
        </td>
    </tr>


    <tr style=\\"\\">
        <td class=\\"\\" style=\\"\\">
            {{footer}}
        </td>
    </tr>

    </tbody>
</table>', '2016-10-13 00:00:00');