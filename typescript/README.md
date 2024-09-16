Interview Assessment
====================

This is a simple assessment to gauge your knowledge of TypeScript and the core
language features of JavaScript. In this assessment, you will be making a simple
_Dependency Injection_ library that is framework-agnostic and can be used in any
JavaScript project. The goal of this assessment is to test your knowledge of
various design patterns and your understanding of the core fundamentals of
JavaScript.

## Problem Statement

You are tasked with creating a simple _Dependency Injection_ library that can be
used in any JavaScript project. The library should allow developers to register
dependencies and resolve them when needed. The library should also support
dependency injection through constructor injection.

## Requirements

| Points | Requirement                                                                                    |
|--------|------------------------------------------------------------------------------------------------|
| 10     | The library should have a `Container` class that contains registered services.                 |
| 5      | The `Container` class should have a `register` method to register services.                    |
| 10     | The `Container` class should have a `compile` method to compile services.                      |
| 10     | The `Container` class should have a `get` method to retrieve services.                         |
| 10     | The `Container`'s `get` method should throw an error if a requested service is not registered. |
| 10     | The library should support constructor injection.                                              |
| 15     | The library should support asynchronous service creation through factories.                    |
| 10     | The library should throw an error if a circular reference is detected when compiling services. |
| 5      | The library should support transient services.                                                 |
| 15     | Services should be registrable with a `@Service` decorator.                                    |
| 15     | Services should be (optionally) resolvable with an `@Inject` decorator on properties.          |
| 25     | The library should be tested with unit tests.                                                  |

### Usage example

```ts
@Service()
class Logger
{
    public log(message: string)
    {
        console.log(message);
    }
}

@Service({
    // Factory function to create the service asynchronously.
    factory: async (constructorArguments) => new ServiceA(...constructorArguments),
})
class ServiceA
{
    constructor(private logger: Logger)
    {
        this.logger.log('ServiceA created');
    }

    public doSomething()
    {
        this.logger.log('ServiceA doing something');
    }
}

@Service()
class ServiceB
{
    constructor(private logger: Logger)
    {
        this.logger.log('ServiceB created');
    }

    public doSomething()
    {
        this.logger.log('ServiceB doing something');

        return this.serviceA.doSomething();
    }
}

const container = await Container.Compile();
// Expected output:
// > ServiceA created
// > ServiceB created

container.get(ServiceB).doSomething();
// Expected output:
// > ServiceB doing something
// > ServiceA doing something
```

### Optional use-case for injection of services in non-service classes.
```ts
export class MyWebComponent
{
   // Allow the service to be injected through a property. Although optional
   // for the assessment, this feature is nice to have to support existing
   // frameworks that do not support constructor injection. For example when
   // working with WebComponents (StencilJS, React, etc.) Note that the class
   // itself doesn't neccessarily need to be a registered service for this to
   // work.
   @Inject private serviceA: ServiceA;
   
   public connectedCallback()
   {
       // This assumes that the container was previously compiled elsewhere in
       // the application. For this assessment, you can assume that the app has
       // one container that is globally available and compiled at the start of
       // the application, e.g. before rendering the first component.
       this.serviceA.doSomething();
   }
}
```

## Submission

You are free to take any approach you see fit to solve the problem, but your
implementation must adhere to the following requirements:

1. Your implementation must be written in TypeScript.
2. No external libraries or frameworks can be used to solve the problem.
3. The only external dependencies you can use are `typescript` and a unit-testing
   library of your choice.

When reviewing your submission, we will be looking for the following:

1. Clean, readable, and maintainable code with a consistent coding style.
2. Proper use of TypeScript's language features and core JavaScript concepts.
3. Proper use of SOLID principles and design patterns with emphasis on separation
   of concerns.
4. Unit tests that cover the core functionality of your implementation.
5. Functional tests that demonstrate the usage of your library written as a small
   example application from the perspective of a developer using your library.

#### _Create a fork of this repository and submit your solution as a pull request._
