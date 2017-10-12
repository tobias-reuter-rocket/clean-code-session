
class Logger {
  constructor(config) {
    // ...
  }

  log(event) {
    // ...
    console.log(`Logged: ${event}`);
  }
}

class MailLogger extends Logger {
  constructor(config) {
    super(config);
  }

  log(event) {
    super.log(event);
    this.sendMail(event);
  }

  sendMail(event) {
    // ...
    console.log(`Mail sent: ${event}`);
  }
}

const config = {};
const mailLogger = new MailLogger(config);
mailLogger.log('The eagle has landed.');
// Logged: The eagle has landed.
// Mail sent: The eagle has landed.
