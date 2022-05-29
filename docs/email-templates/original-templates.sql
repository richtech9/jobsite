CREATE TABLE guowangm_wrdp5_millionsusers.wp_email_templates
(
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  subject varchar(255) NOT NULL,
  content longtext NOT NULL,
  modified_date datetime NOT NULL
);
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (1, 'Account Activation', '{{activation_link}}', 'Account Activation - FreeLinguist', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\"> </td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Welcome to PeerOK! Click the link below to activate your account. </td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{activation_link}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\"> </td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (2, 'Forgot Password', '{{password}}', 'Reset Your Password', 'Your new password is: {{password}}.

Please click this link to reset the password.', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (3, 'Translated Document', '{{job_title}}', 'Service Document  Submitted', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Service Document Submitted</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">
<p><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{job_title}}</span></p>
</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">
<p><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\"><strong>Information</strong></span></p>
</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">
<p><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">A new service document has been uploaded by the freelancer for {{job_title}}.</span></p>
</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-15 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (4, 'A new doc has been submitted and You have  to reply within the time limit', '{{job_title}}', 'New service submitted by freelancer', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">New service submitted by freelancer</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">The freelancer has submitted service. Please review within the time limit: {{job_title}}.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (5, 'New revision request', ' {{job_title}}', 'New revision request', '<p>A new revision request has been submitted: {{job_title}}. Please respond within time limit.</p>
<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">New revision request</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">A new revision request has been submitted: {{job_title}}. Please respond within time limit.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">Please respond within time limit.</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (6, 'Hire Translate', '{{job_path}}', 'Congratulations! You are hired!', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Congratulations! You are hired!</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">
<table class=\\"bodyTable\\">
<tbody>
<tr>
<td><span class=\\"barcode-text\\">Congratulations! You have been hired for job {{job_path}}. Please respond to the hiring as soon as possible.</span></td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (7, 'Tips ', '{{job_path}}', 'Tips received ', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Tips received</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">You have received tips.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (8, 'Bonus', '{{job_path}}', ' Bonus received', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Bonus received</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">You have received bonus: {{job_path}}</span></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (9, 'Linguist Payment Received', '{{job_path}}', 'Job completed', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Job completed</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Congratulations, your job has been approved by the customer. The payment for this job has been sent to your account.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (10, 'Customer posted review', '{{job_path}}', 'Customer has posted review ', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\"> </td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Your customer has posted review on your job</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\"> </td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (11, 'W9 Form', '', 'W9 Form', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\"> </td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Please fill and sign in your W-9 form using the following link.</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\"><a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\" href=\\"https://cudasign.com/s/WQvjviUo\\" target=\\"_blank\\">W-9 form</a></span></td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">After submission, the signed W-9 form will be emailed to you. Please upload the signed W-9 form to our website on the Wallet page.The form illustrations might be helpful to understand how to fill in the form.<br />
<a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\" href=\\"https://cudasign.com/s/WQvjviUo\\" target=\\"_blank\\">W-9 form illustrations</a></span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\"> </td>
</tr>
</tbody>
</table>', '2016-01-17 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (12, 'W8BEN Form', '', 'W8BEN Form', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\"> </td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">W8BEN Form</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Please fill and sign in your W-9 form using the following link.</span></td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\"><a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\" href=\\"https://cudasign.com/s/aMatUbjJ\\" target=\\"_blank\\">W-8BEN</a></span></td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">
<p><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">After submission, the signed W-8BEN form will be emailed to you. Please upload the signed W-8BEN form to our website on the Wallet page.</span></p>
<p>The following illustrations might be helpful for you to fill in the form.<br />
 <a style=\\"text-decoration: none; font-size: 17px; font-weight: bold; padding: 15px; background: #2a8ac7; margin: 0; color: #fff; border-radius: 5px; display: inline-block; text-align: center;\\" href=\\"https://www.youtube.com/watch?v=K0Sy_cvWsG0\\" target=\\"_blank\\">W-8BEN form illustrations.</a></p>
</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\"> </td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (13, 'Tax Form', '', 'Tax Form', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\"> </td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Hello</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Please find the form</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\"> </td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (14, 'Bid Statement', ' {{job_title}},  {{job_path}}', 'Bid Statement', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\"> </td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Bid Statement</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">A new bid on the job {{job_title}} is waiting for your approval.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\"> </td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (15, 'New request Evaluation recieved', '{{name}}', 'A new Request Evaluation is received', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">A new Request Evaluation is received</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{name}} </span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-05 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (16, 'Admin receve Withdraw Request', '{{user_email}}, {{withdrawl_amount}}', 'Withdraw Amount', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Withdraw Amount</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">
<p>&nbsp;</p>
<p>{{user_email}} sends a withdraw request for {{withdrawl_amount}}.</p>
<p>&nbsp;</p>
</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (17, 'Email Change', '{{activation_link}}', 'New acivation link to change email', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Here is the link for you to change email:</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">      {{activation_link}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (18, 'Linguist Rejected job', '{{cancel_service_note}}, {{job_title}}', 'The freelancer has rejected the job', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">The freelancer has rejected the job:- {{job_title}}</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">      Cancel Service Note: {{cancel_service_note}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (19, 'JOB Advance payment to freelancer', '{{job_title}}', 'Advance payment for your new job', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Advance payment for your new job</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Advance payment has been credited in your account for {{job_title}}.</span></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (20, 'Linguist start working on  job', '{{job_title}}', 'The freelancer has started working on your job', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Congratulations!</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">The freelancer has started working on your job {{job_title}}.</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (21, 'Refill Account by admin', '{{refill_amount}},{refill_message}},{{transaction_id}}', 'Refill account by PeerOK administrator', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">{{refill_amount}} USD has been refilled to your account by PeerOK.</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Transaction ID: {{transaction_id}}.</span></td>
</tr>
<tr>
<td>     Refill note from FreeLinguist: {{refill_message}}.</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (22, 'Request Evalutaion accepted', '{{translation_level}}, {{translation_per_word_earning}}, {{translation_bonus_tip_percentage}},
{{editing_level}},
{{editing_per_word_earning}}, {{editing_bonus_tip_percentage}},
{{writing_level}},
{{writing_per_word_earning}},
{{writing_bonus_tip_percentage}}', 'Congratulation! Your application has been approved.', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Congratulation!</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">After thorough evaluation of your qualifications, we are excited to notify you that you have been approved to provide linguistic services on FreeLinguist.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">Below please find your technical levels for each service type and the corresponding payment percentages. </span></td>
</tr>
<tr>
<td>Translation level: {{translation_level}}</td>
</tr>
<tr>
<td>Base payment percentage: {{translation_per_word_earning}} </td>
</tr>
<tr>
<td>Percentage of bonus and tip payment: {{translation_bonus_tip_percentage}} </td>
</tr>
<tr>
<td>Editing/proofreading level: {{editing_level}} </td>
</tr>
<tr>
<td>Base payment percentage: {{editing_per_word_earning}} </td>
</tr>
<tr>
<td>Percentage of bonus and tip payment: {{editing_bonus_tip_percentage}} </td>
</tr>
<tr>
<td>Writing level: {{writing_level}} </td>
</tr>
<tr>
<td>Base payment percentage: {{writing_per_word_earning}} </td>
</tr>
<tr>
<td>Percentage of bonus and tip payment: {{writing_bonus_tip_percentage}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (23, 'Automatic canceled job : Customer not selected any freelancer', '{{days}}, {{job_path}}', 'Automatic canceled job', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Automatic canceled job</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">The job has been canceled since you have not selected any freelancer for {{days}} days.</span></td>
</tr>
<tr>
<td>{{job_path}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (24, 'Customer: Freelancer does not respond by selecting either START SERVICE or REJECT SERVICE', '{{hours}}, {{job_path}}', 'Automatic rejection of job ', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Hello,</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Linguist does not respond by selecting either START SERVICE or</span></td>
</tr>
<tr>
<td>REJECT SERVICE within {{hours}} hours, the job is automatically rejected for the Freelancer. {{job_path}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (25, 'Lingusit: Linguist does not respond by selecting either START SERVICE or REJECT SERVICE', '{{hours}}, {{job_path}}', 'Automatic rejected for the linguist', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Automatic rejected for the linguist</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Since you haven\\''t responded to the job hiring invitation by selecting either START SERVICE or <br />
REJECT SERVICE within {{hours}} hours, the job is automatically rejected for you.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (26, 'The job is automatically approval if customer do not approve completion', '{{hours}}, {{job_path}}', 'Job has been approved', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Job has been approved</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">The job has been automatically approved since you have not approved completion or requested revision within {{hours}} hours after the linguist has submitted service files..</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (27, 'LOST BID DEPOSIT', '{{job_path}}', 'No timely response to job and forfeit of BID DEPOSIT', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">No timely response to job and forfeit of BID DEPOSIT</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">You have not responded timely to the job. The bid deposit has been forfeited.</span></td>
</tr>
<tr>
<td>{{job_path}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (28, 'x days reminder email', '{{job_delivery_date}}, {{job_path}}', 'Reminder Email: Job Delivery Soon', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Reminder Email: Job Delivery Soon</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">The job is expected to be delivered on {{job_delivery_date}}.</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (29, 'y days reminder email', '{{job_delivery_date}}, {{job_path}}', 'Reminder Email: Job Delivery Soon', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Reminder Email Job Delivery Soon</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">The job has to be delivered by {{job_delivery_date}}.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_path}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (30, 'Send Message to customer or linguist  by Evaluation Sub admin', '{{message}}', 'Send Message to customer or linguist  by Evaluation Sub admin', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Send Message to customer or linguist by Evaluation Sub admin</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"> </td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{message}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (31, 'To Receive Notifications Of New Available Jobs(HOURLY)', '{{new_available_job}}', 'Posts Of New Jobs', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">New Jobs have been posted!</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{new_available_job}}</span></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (32, 'To Receive Notifications Of New Available Jobs(DAILY)', '{{new_available_job}}', 'New Jobs Posted', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">New Jobs have been posted.</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{new_available_job}}</span></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (33, 'Account Close', '', 'Account has been closed', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Your Account has been closed.</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (34, 'Account Activated', '', 'Account Activated', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Account Activated</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (35, 'PLACE NEW ORDER OR JOB', '{{job_link}},{{order_placed_date}}
', 'A new order has been placed ', '<table>
    <tbody>

    <tr style=\\"\\">
        <td style=\\"\\">
            {{header}}
        </td>
    </tr>

    <tr style=\\"height: 57px;\\">
        <td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular,serif; height: 57px;\\">
            Your order has been placed
        </td>
    </tr>

    <tr>
        <td>
            <span class=\\"barcode-text\\">Order Placed : {{order_placed_date}}</span>
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



    <tr style=\\"height: 27px;\\">
        <td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">
            If you don\\''t want to receive these emails from PeerOK. Please visit your
            <a href=\\"{{notification_settings_url}}\\" target=\\"_blank\\"
            >
                email settings page
            </a>
        </td>
    </tr>
    </tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (36, 'CANCEL ORDER OR JOB', '{{job_link}}', 'Order cancelled', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Your order has been canceled.</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"> </td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_link}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (37, 'Re place the order', '{{job_link}}', 'Re-place the order', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Your order has been re-placed.</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_link}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (38, 'Mediation has been started', '{{job_path}}', 'Mediation has been started', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Mediation has been started</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">An independent mediator has been hired to mediate the case. The mediator will carefully consider any information presented and contact you shortly for an agreeable solution. If you want to present any additional information other than those in the Job Details, please email them to: dispute@freelinguist.com within 5 business days.</span></td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_title}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (39, 'Receive Messgae', '{{url}}', 'Receive Messgae - FreeLinguist', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Welcome to PeerOK! Receive Message</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{utlth}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (40, 'Request Change', '{{request_change}}', 'Request Change', '<p>{{request_change}}</p>
<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Request Change</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{request_change}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (41, 'Email to linguist for bidding deposit is refunded when job cancel', '{{job_title}}', 'Bidding deposit is refunded', '
Unfortunately, Job {{job_title}}  has been cancelled. Your bidding security deposit has been fully refunded.', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (42, 'Email to user Refill credit', '{{credit}} {{processing_fee}} {{total_amount}}', 'Credits have been refilled. ', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Credits have been refilled.</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Your have refilled credits</span></td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{credit}} - {{processing_fee}} ={{total_amount}} USD.</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (43, 'Email to linguist when places bid on a job
', '{{job_title}}', 'You have placed a new bid', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">You have placed a new bid on the job</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_title}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (45, 'Approve Rejection by linguist', '{{job_name}}', 'Approve Rejection by linguist', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Approve Rejection by linguist</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_name}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (46, 'Job Rejection Request', '{{job_name}}', 'Job Rejection Request', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Job Rejection Request</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_name}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (47, 'Job Rejection Request By Admin', '{{job_name}}', 'Job Rejection Request By Admin', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Job Rejection Request By Admin</td>
</tr>
<tr>
<td><span class=\\"barcode-text\\">{{job_name}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (48, 'Approve Rejection by admin', '{{job_name}}', 'Approve Rejection by admin', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Approve Rejection by admin</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">{{job_name}}</span></td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (49, 'Mediation has been started Hire By admin', '{{job_path}}', 'Mediation has been started Hire By admin', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Mediation has been started</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">An independent mediator has been hired to mediate the case. The mediator will carefully consider any information presented and contact you shortly for an agreeable solution. If you want to present any additional information other than those in the Job Details, please email them to: dispute@freelinguist.com within 5 business days.</span></td>
</tr>
<tr>
<td>{{job_path}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-14 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (50, 'Email to linguist for bidding deposit is refunded When Approve job', '{{job_title}}', 'Bidding deposit is refunded', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Bidding deposit is refunded</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">Job has been approved completed. Your bidding security deposit has been fully refunded.</td>
</tr>
<tr>
<td> {{job_title}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (51, 'Email to linguist for bidding deposit is refunded When customer hire another job', '{{job_title}}', 'Bidding deposit is refunded', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Bidding deposit is refunded</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">Customer hire another linguist. Your bidding security deposit has been fully refunded. </td>
</tr>
<tr>
<td>{{job_title}}</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (52, 'Customer Partially Job has been completed', '{{job_title}}
{{partially_percentage}}', 'Congratulations! Job has been completed', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Congratulations! Job has been completed</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">Congratulations! Your job {{job_title}} has been {{partially_percentage}} percent partially approved completion by you.</td>
</tr>
<tr>
<td>You have received the corresponding refund.</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (53, 'Linguist Partially Job has been completed', '{{job_title}}
{{partially_percentage}}', 'Congratulations! Job has been partially approved', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">Congratulations! Job has been partially approved</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\">Congratulations! Your job {{job_title}} has been {{partially_percentage}} percent partially approved completion.</td>
</tr>
<tr>
<td>You have received {{partially_percentage}} percent of total expected earnings.</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (54, 'AUTOMATIC JOB REJECTTION APPROVE EMAIL TO LINGUIST', '{{job_name}}', 'AUTOMATIC JOB REJECTTION APPROVE EMAIL TO LINGUIST', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">AUTOMATIC JOB REJECTTION APPROVE EMAIL TO LINGUIST</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Since you haven\\''t replied timely, Job {{job_name}} has been rejected automatically. The security deposit has been forfeited following our Terms of Service.</span></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');
INSERT INTO guowangm_wrdp5_millionsusers.wp_email_templates (id, title, description, subject, content, modified_date) VALUES (55, 'AUTOMATIC JOB REJECTTION APPROVE EMAIL TO CUSTOMER', '{{job_name}}', 'AUTOMATIC JOB REJECTTION APPROVE EMAIL TO CUSTOMER', '<table>
<tbody>
<tr style=\\"background: rgba(234, 163, 50, 0.48); height: 24px;\\">
<td class=\\"topBar\\" style=\\"text-align: center; height: 24px;\\">If you can’t read this email.Please <a href=\\"#\\" target=\\"_blank\\">view online</a></td>
</tr>
<tr style=\\"height: 101px;\\">
<td style=\\"height: 101px;\\">
<div class=\\"logo\\" style=\\"padding: 20px;\\"><a href=\\"http://test.com/\\" target=\\"_blank\\"><img src=\\"/wp-content/uploads/2017/01/1483448238/logo.png\\" alt=\\"\\" height=\\"50\\" border=\\"0\\" /><sup style=\\"top: -38px; position: relative;\\">®</sup></a></div>
</td>
</tr>
<tr style=\\"height: 57px;\\">
<td class=\\"main-heading\\" style=\\"padding: 20px; font-size: 38px; color: #333333; font-family: opensansregular; height: 57px;\\">AUTOMATIC JOB REJECTTION APPROVE EMAIL TO CUSTOMER</td>
</tr>
<tr style=\\"height: 27px;\\">
<td style=\\"height: 27px;\\"><span class=\\"barcode-text\\" style=\\"padding: 20px; font-size: 18px; color: #333; font-family: opensansregular;\\">Job {{job_name}} has been successfully rejected and you have been fully refunded</span></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr style=\\"height: 5px;\\">
<td style=\\"height: 5px;\\">
<hr style=\\"border-top: 2px solid rgba(42, 138, 199, 0.6); background: rgba(234, 163, 50, 0.48); padding-bottom: 15px; margin-top: 2%; margin-bottom: 1%;\\" /></td>
</tr>
<tr style=\\"height: 27px;\\">
<td class=\\"unsubscribe\\" style=\\"text-align: center; font-size: 13px;\\">If you don\\''t want to receive these emails from PeerOK.Please <a href=\\"#\\" target=\\"_blank\\">unsubscribe</a> PeerOK</td>
</tr>
</tbody>
</table>', '2016-10-13 00:00:00');