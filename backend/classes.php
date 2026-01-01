<?php 
    class Car implements JsonSerializable{
        private string $number_plate;
        private string $VIN;
        private Manufacturer $manufacturer;
        private Model $model;
        private CarType $type;
        private string $color;
        private float $rental_rate;

        public function __construct(
            string $number_plate,
            string $VIN,
            Manufacturer $manufacturer, // Expecting an object
            Model $model,               // Expecting an object
            CarType $type,             // Expecting an object
            string $color,
            float $rental_rate
            ) {
                $this->number_plate = $number_plate;
                $this->VIN = $VIN;
                $this->manufacturer = $manufacturer;
                $this->model = $model;
                $this->type = $type;
                $this->color = $color;
                
                $this->rental_rate = $rental_rate;
            }

        // Getters
        public function getNumberPlate(): string { return $this->number_plate; }
        public function getVIN(): string { return $this->VIN; }
        public function getManufacturer(): Manufacturer { return $this->manufacturer; }
        public function getModel(): Model { return $this->model; }
        public function getType(): CarType { return $this->type; }
        public function getColor(): string { return $this->color; }
        public function getRentalRate(): float { return $this->rental_rate; }

        // Setters (Example of a core setter)
        public function setRentalRate(float $rate): void { $this->rental_rate = $rate; }
        public function setColor(string $color): void { $this->color = $color; }

        public function jsonSerialize(): mixed
        {
            return ['number_plate'=> $this->number_plate,
                    'VIN'=> $this->VIN,
                    'colour'=>$this->color,
                    'rental_rate'=>$this->rental_rate];
        }
    }

    class Manufacturer {
        private int $manufacturer_id;
        private string $name;

        public function __construct(int $id, string $name) {
            $this->manufacturer_id = $id;
            $this->name = $name;
        }

        // Getters
        public function getManufacturerId(): int { return $this->manufacturer_id; }
        public function getName(): string { return $this->name; }

        // Setters (less common for ID/Name, but included for completeness)
        public function setName(string $name): void { $this->name = $name; }
    }

    //car model
    class Model {
        private int $model_id;
        private int $manufacturer_id;
        private int $year;
        private string $name;
        private int $number_of_seats;
        private ?float $tow_capacityKG;

        public function __construct(int $model_id, int $manufacturer_id, int $year, string $name,int $number_of_seats,
            ?float $tow_capacityKG) {
            $this->model_id = $model_id;
            $this->manufacturer_id = $manufacturer_id;
            $this->year = $year;
            $this->name = $name;
            $this->number_of_seats = $number_of_seats;
            $this->tow_capacityKG = $tow_capacityKG;
        }

        // Getters
        public function getModelId(): int { return $this->model_id; }
        public function getManufacturerId(): int { return $this->manufacturer_id; }
        public function getYear(): int { return $this->year; }
        public function getName(): string { return $this->name; }
        public function getNumberOfSeats(): int { return $this->number_of_seats; }
        public function getTowCapacityKG(): ?float { return $this->tow_capacityKG; }
        // Setters
        public function setName(string $name): void { $this->name = $name; }
        public function setYear(int $year): void { $this->year = $year; }
    }

   class CarType {
    private int $type_id;
    private string $typeName;
    private ?string $description;

    public function __construct(int $type_id, string $typeName, ?string $description) {
        $this->type_id = $type_id;
        $this->typeName = $typeName;
        $this->description = $description;
    }

        // Getters
    public function getTypeId(): int { return $this->type_id; }
    public function getTypeName(): string { return $this->typeName; }
    public function getDescription(): ?string { return $this->description; }

    // Setters
    public function setTypeName(string $typeName): void { $this->typeName = $typeName; }
    public function setDescription(?string $description): void { $this->description = $description; }
    }


   class User {
        private int $user_id;
        private string $email;
        private string $phone_number;
        private string $usertype; // ENUM 'N' or 'A'

        public function __construct(int $user_id, string $email, string $phone_number, string $usertype) {
        $this->user_id = $user_id;
        $this->email = $email;
        $this->phone_number = $phone_number;
        $this->usertype = $usertype;
        }   

        // Getters
        public function getUserId(): int { return $this->user_id; }
        public function getEmail(): string { return $this->email; }
        public function getPhoneNumber(): string { return $this->phone_number; }
        public function getUserType(): string { return $this->usertype; }

        // Setters
        public function setEmail(string $email): void { $this->email = $email; }
        public function setPhoneNumber(string $phone_number): void { $this->phone_number = $phone_number; }
    }

   class Customer {
    private User $user_details;
    private string $physical_address;
    private string $id_hash; // Your low-risk security compromise
    private string $next_of_kin_contact;

        public function __construct(
        User $user_details,
        string $physical_address,
        string $id_hash,
        string $next_of_kin_contact
        ) {
            $this->user_details = $user_details;
            $this->physical_address = $physical_address;
            $this->id_hash = $id_hash;
            $this->next_of_kin_contact = $next_of_kin_contact;
        }

        // Getters
        public function getUserDetails(): User { return $this->user_details; }
        public function getPhysicalAddress(): string { return $this->physical_address; }
        public function getIdHash(): string { return $this->id_hash; }
        public function getNextOfKinContact(): string { return $this->next_of_kin_contact; }

        // Setters
        public function setPhysicalAddress(string $address): void { $this->physical_address = $address; }
        public function setNextOfKinContact(string $contact): void { $this->next_of_kin_contact = $contact; }
    }

   class Rental {
    
    // --- Primary Key and Foreign Keys ---
    private int $rentID;           // Primary Key
    private int $userId;           // FK to Users
    private string $VIN;           // FK to Cars
    
    // --- Date and Time Data ---
    private DateTime $startDate;
    private DateTime $endDate;      // Expected return date
    private ?DateTime $returnDate;  // Actual return date (Nullable)
    
    // --- Financial Tracking (Simplified for manual payments) ---
    private float $dailyRateUsed;
    private float $expectedTotalCost;
    private float $depositAmount;
    private float $totalPaid;
    private string $paymentMethod;
    
    // --- Status and Control ---
    private string $rentalStatus; // ENUM: BOOKED, PICKED_UP, RETURNED, etc.

    /**
     * Constructor: Initializes a Rental object from an array of database data.
     * @param array $data An associative array containing rental details.
     */
    public function __construct(array $data) {
        
        // Map data types and handle nulls
        $this->rentID = (int)$data['RentID'];
        $this->userId = (int)$data['userId'];
        $this->VIN = $data['VIN'];
        
        // Use DateTime objects for robust date handling
        $this->startDate = new DateTime($data['start_date']);
        $this->endDate = new DateTime($data['end_date']);
        
        // Handle nullable return date
        $this->returnDate = $data['return_date'] ? new DateTime($data['return_date']) : null;
        
        // Financials (ensure float casting)
        $this->dailyRateUsed = (float)$data['daily_rate_used'];
        $this->expectedTotalCost = (float)$data['expected_total_cost'];
        $this->depositAmount = (float)$data['deposit_amount'];
        $this->totalPaid = (float)$data['total_paid'];
        $this->paymentMethod = $data['payment_method'];

        $this->rentalStatus = $data['rental_status'];

        // Optionally, add logic to calculate any missing fields here
    }

    // --- Essential Methods (Business Logic) ---
    
    /**
     * Calculates the number of days the car was actually rented.
     */
    public function calculateDurationDays(): int {
        $end = $this->returnDate ?? new DateTime('now'); // Use actual return or today if active
        $interval = $this->startDate->diff($end);
        return $interval->days;
    }

    /**
     * Determines if the rental is currently overdue.
     */
    public function isOverdue(): bool {
        // Only check if it hasn't been returned yet
        if ($this->rentalStatus === 'PICKED_UP') {
            $now = new DateTime('now');
            return $now > $this->endDate;
        }
        return false;
    }

    // ... (All necessary public getter/setter methods)
    }
?>