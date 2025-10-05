<?php

namespace Tcds\Io\Serializer\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\JsonObjectMapper;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\SerializerTestCase;

class TypeNodeTest extends SerializerTestCase
{
    private const string ARRAY_LIST_OF_ADDRESS_TYPE_NODE = <<<JSON
    {
      "type": "Tcds\\\\Io\\\\Generic\\\\ArrayList<Tcds\\\\Io\\\\Serializer\\\\Fixture\\\\ReadOnly\\\\Address>",
      "params": {
        "items": {
          "name": "items",
          "node": {
            "type": "list<Tcds\\\\Io\\\\Serializer\\\\Fixture\\\\ReadOnly\\\\Address>",
            "params": {
              "value": {
                "name": "value",
                "node": {
                  "type": "Tcds\\\\Io\\\\Serializer\\\\Fixture\\\\ReadOnly\\\\Address",
                  "params": {
                    "street": {
                      "name": "street",
                      "node": {
                        "type": "string",
                        "params": []
                      }
                    },
                    "number": {
                      "name": "number",
                      "node": {
                        "type": "int",
                        "params": []
                      }
                    },
                    "main": {
                      "name": "main",
                      "node": {
                        "type": "bool",
                        "params": []
                      }
                    },
                    "place": {
                      "name": "place",
                      "node": {
                        "type": "Tcds\\\\Io\\\\Serializer\\\\Fixture\\\\ReadOnly\\\\Place",
                        "params": {
                          "city": {
                            "name": "city",
                            "node": {
                              "type": "string",
                              "params": []
                            }
                          },
                          "country": {
                            "name": "country",
                            "node": {
                              "type": "string",
                              "params": []
                            }
                          },
                          "position": {
                            "name": "position",
                            "node": {
                              "type": "Tcds\\\\Io\\\\Serializer\\\\Fixture\\\\ReadOnly\\\\LatLng",
                              "params": {
                                "lat": {
                                  "name": "lat",
                                  "node": {
                                    "type": "float",
                                    "params": []
                                  }
                                },
                                "lng": {
                                  "name": "lng",
                                  "node": {
                                    "type": "float",
                                    "params": []
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    JSON;

    #[Test] public function load_node_from_json(): void
    {
        $mapper = new JsonObjectMapper();
        $node = TypeNode::from(generic(ArrayList::class, [Address::class]));
        $this->initializeLazyParams($node);

        $read = $mapper->readValue(TypeNode::class, self::ARRAY_LIST_OF_ADDRESS_TYPE_NODE);

        $this->assertEquals($node, $read);
    }
}
