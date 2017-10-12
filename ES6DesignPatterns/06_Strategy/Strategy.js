
class DeliveryCalculator {
  constructor(calculationStrategy) {
    this.calculationStrategy = calculationStrategy;
  }

  calculate(order) {
    return this.calculationStrategy.calculate(order);
  }
}

class DHLStrategy {
  calculate(order) {
    // calculations...
    return 19.00;
  }
}

class DeutschePostStrategy {
  calculate(order) {
    // calculations...
    return 15.35;
  }
}

const order = { /* actual Order */ };
const calculationStrategy = new DHLStrategy();
const deliveryCalculator = new DeliveryCalculator(calculationStrategy);

const deliveryCost = deliveryCalculator.calculate(order);
// 19.00


