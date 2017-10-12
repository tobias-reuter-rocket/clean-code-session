
class Logger {
  // ...
  log(event) {
    console.log(`Logged: ${event}`);
  }
}

class MailSender {
  // ...

  sendMail(event) {
    console.log(`Mail sent: ${event}`);
  }
}

class LoggerMailAdapter {
  constructor(mailSender) {
    this.mailSender = mailSender;
  }

  log(event) {
    this.mailSender.sendMail(event);
  }
}

const mailSender = new MailSender();
const logger = new LoggerMailAdapter(mailSender);

logger.log('The eagle has landed.');
// Mail sent: The eagle has landed.
