<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\ConditionalCheckFailedException;

class Rooms_model {
	protected $primaryKey = 'roomid';
	protected $tableName = 'EN_Rooms';
	protected $client;
    function __construct() {
        $args = array(
        'version' => 'latest',
        // 'version' => '2012-08-10',
        'region' => 'us-east-1',
        
        'credentials' => array(
            'key'    => '#yourkey',
            'secret' => '#secret'
        )
            
        );
        $this->client = DynamoDbClient::factory($args);
		
    }

    function table_exists(){
		// Instantiate the class.
        
		$response = $this->client->describeTable(array(
			'TableName' => $this->tableName
		));
		if ((string) $response['Table']['TableStatus'] === 'ACTIVE'){
			return true;
		} else {
			return false;
		}
	}
    	/** Fetches all records with limit and orderby values's */
	function getAll($limit='',$filter='') {
		//$dynamodb = new AmazonDynamoDB();
        $query = array(
			'TableName' => $this->tableName,
		);
        if ($limit != ''){$query['Limit'] = $limit;}
        //print_r($query);
        if (empty($filter)) {
            $scan_response = $this->client->scan($query);
                return $this->convertItems($scan_response['Items']);

        } else {
            $scanFilter = $this->convertConditions($filter);
            $query['ScanFilter'] = $scanFilter;
            $items = $this->client->getIterator('Scan', $query);
            return $this->convertItems($items);
        }
        
    }
    /** Fetches a record by its' passed field and values's */
    function getByID($id='') {

		if ($id != ''){
			//$dynamodb = new AmazonDynamoDB();
			$query = array(
				'TableName' => $this->tableName, 
                'Key' => array(
                    "roomid" => array(
                        "N" => $id
                    )
                )
			);
			$scan_response = $this->client->getItem($query);
            //print_r($scan_response);
			if ($scan_response['status'] == '200'){
				 return $scan_response->body->Item;
			} else {
				print_r($scan_response['Item']);
                return $this->convertItem1($scan_response['Item']);
			}
		}
        return false;
    }
    	/** Insert new record */
	function save($member='') { 

		if ($member != ''){
			if (!isset($member['roomid'])){ // new record
				$id = time().rand(0,100);
				$queue = array(
					'TableName' => $this->tableName,
                    "Item" => array(
                        "roomid" => array(
                            "N" => $id
                        ),
                        "title" => array(
                            "S" => $member['title']
                        ),
                        "owner" => array(
                            "S"=> $member['owner'],
                        ),
                        "background" => array(
                            "S"=> $member['background']
                        )
                    )
				);
					 	
				// Execute the batch of requests in parallel if you wish
				$responses = $this->client->putItem($queue);
                    return $id;
			} else { // edit existing record
				$id = $member['roomid'];
                echo "<br><hr>";
                $dataupdate = array(
					'TableName' => $this->tableName,
					'Key' => array(
                        "roomid" => array(
                            "N" => $id
                        )
                    ),
                    'ExpressionAttributeNames' => array(
                        '#title' => 'title',
                        '#owner' => 'owner',
                        '#background' => 'background',
                    ),
                    "ExpressionAttributeValues" => array(
                        ":title" => array("S"=>  $member['title']),
                        ":owner" => array("S"=>  $member['owner']),
                        ":background" => array("S"=>  $member['background'])
                    ),
                    "ReturnValues" => "ALL_NEW",
                    "UpdateExpression" => "set #title = :title, #owner = :owner, #background = :background",
					
				);
				$response = $this->client->updateItem($dataupdate);
                    return $this->convertItem1($response['Item']);
			}
		}
    }

    /** Deletes a record by it's primary key */
    function deleteById($id='') {
		if ($id != ''){
			$response = $this->client->deleteItem(array(
				'TableName' => $this->tableName,
				'Key' => array(
                        "roomid" => array(
                            "N" => $id
                        )
                    ),
			));
            //print_r($response);
            echo "room ".$id." deleted<br>";
				return true;
		}
		return false;
    }


    // Define the custom sort function
    function custom_sort($a,$b) {
        return $a['roomid']>$b['roomid'];
    }

    function convertItems($items)
    {
        $converted = array();
        foreach ($items as $item) {
            //if (empty($item)) return null;
            $converted []= $this->convertItem($item);
        }
        return $converted;
    }
    function convertItem1($item)
    {
        $converted = array();
        if($item){
            $converted []= $this->convertItem($item);
        }
        return $converted;
    }
     function convertItem($item)
    {
        if (empty($item)) return null;
        $converted = array();
        foreach ($item as $k => $v) {
           
            if (isset($v['S'])) {
                $converted[$k] = $v['S'];
            }
            else if (isset($v['SS'])) {
                $converted[$k] = $v['SS'];
            }
            else if (isset($v['N'])) {
                $converted[$k] = $v['N'];
            }
            else if (isset($v['NS'])) {
                $converted[$k] = $v['NS'];
            }
            else if (isset($v['B'])) {
                $converted[$k] = $v['B'];
            }
            else if (isset($v['BS'])) {
                $converted[$k] = $v['BS'];
            }
            else if (isset($v['BOOL'])) {  //Lane Added 
                $converted[$k.'::BOOL'] = $v['BOOL'];
            }
            else {
                throw new Exception('Not implemented type : '.$k);
            }
        }
        return $converted;
    }

	function convertConditions($conditions)
    {
        $ddbConditions = array();
        foreach ($conditions as $k => $v) {
            // Get attr name and type
            $attrComponents = $this->convertComponents($k);
            $attrName = $attrComponents[0];
            $attrType = $attrComponents[1];
            // Get ComparisonOperator and value
            if ( ! is_array($v)) {
                $v = array('EQ', $this->asString($v));
            }
            $comparisonOperator = $v[0];
            $value = count($v) > 1 ? $v[1] : null;
            // Get AttributeValueList
            if ($v[0] === 'BETWEEN') {
                if (count($value) !== 2) {
                    throw new Exception("Require 2 values as array for BETWEEN");
                }
                $attributeValueList = array(
                    array($attrType => $this->asString($value[0])),
                    array($attrType => $this->asString($value[1]))
                );
            } else if ($v[0] === 'IN') {
                $attributeValueList = array();
                foreach ($value as $v) {
                    $attributeValueList[] = array($attrType => $this->asString($v));
                }
            } else if ($v[0] === 'NOT_NULL' || $v[0] === 'NULL') {
                $attributeValueList = null;
            } else {
                $attributeValueList = array(
                    array($attrType => $this->asString($value)),
                );
            }
            // Constract key condition for DynamoDB
            $ddbConditions[$attrName] = array(
                'AttributeValueList' => $attributeValueList,
                'ComparisonOperator' => $comparisonOperator
            );
        }
        return $ddbConditions;
    }

	

	
	/** Example using the count function */
	function countAll(){
		$dynamodb = new AmazonDynamoDB();
		$query = array(
			'TableName' => $this->tableName
		);
		$query['Count'] = true;
		$scan_response = $dynamodb->scan($query);
		if ($scan_response->status == '200'){
			 return $scan_response->body->ScannedCount;
		} else {
			print_r($scan_response);
            
		}
	}
	
    
    /** Fetches a record by its' passed field and values's */
    function getByColumn($field='id', $value='') {
		$dynamodb = new AmazonDynamoDB();
		$scan_response = $dynamodb->scan(array(
			'TableName' => $this->tableName, 
			//'AttributesToGet' => array('name'),
			'ScanFilter' => array( 
				$field => array(
					'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
					'AttributeValueList' => array(
						array( AmazonDynamoDB::TYPE_STRING => (string)$value )
					)
				),
			)
		));
		if ($scan_response->status == '200'){
			return $scan_response->body->Items;
		} else {
			print_r($scan_response);
		}
    }
	
    
	
	

	
	function create_table(){
		// Instantiate the class.
		$dynamodb = new AmazonDynamoDB();
		$response = $dynamodb->create_table(array(
			'TableName' => $this->tableName,
			'KeySchema' => array(
				'HashKeyElement' => array(
					'AttributeName' => $this->primaryKey,
					'AttributeType' => AmazonDynamoDB::TYPE_NUMBER
				)
				/* ONLY REQUIRED IF WANTING A Hash and Range table
				,
				'RangeKeyElement' => array(
					'AttributeName' => 'date',
					'AttributeType' => AmazonDynamoDB::TYPE_NUMBER
				)*/
			),
			'ProvisionedThroughput' => array(
				'ReadCapacityUnits' => 5,
				'WriteCapacityUnits' => 5
			)
		));
			 
		// Check for success...
		if ($response->isOK()){
			// continue
		} else {
			echo '# A ERROR HAS OCCURED<br />';
			print_r($response);
			return false;
		}  
			 
		####################################################################
		# Sleep and poll until the table has been created
			 
		$count = 0;
		do {
			sleep(1);
			$count++;
			$response = $dynamodb->describe_table(array(
				'TableName' => $this->tableName
			));
		}
		while ((string) $response->body->Table->TableStatus !== 'ACTIVE');
		return true;
	}
}
