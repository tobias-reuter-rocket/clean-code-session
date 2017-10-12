class Hero {
  constructor(name) {
    this.name = name;
  }

  greet() {
    console.log(`Hi, I'm ${this.name}`);
  }
}

const kickAss = new Hero('Kick-Ass');
kickAss.greet(); // Hi, I'm Kick-Ass
