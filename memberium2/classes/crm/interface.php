<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light
 */


 class_exists('m4is_r83' )||die();
 m4is_c69807::m4is_i702();
 final 
class m4is_c69807 {
 private static $m4is_r1546;
 private static $m4is_r9613;
 private static $m4is_m810;
 private static $m4is_z59682;
 private static $m4is_z98;
 private static $m4is_u29516;
 public static 
function m4is_i702(){
self::$m4is_r1546 =m4is_r83::m4is_c26();
 self::$m4is_r9613 =self::$m4is_r1546->m4is_i76('appname' );
 self::$m4is_z59682 =self::$m4is_r1546->m4is_r1476();
 self::$m4is_z98 =1000;
 self::$m4is_u29516 =self::m4is_o8742();
 self::$m4is_m810 =self::m4is_j9368();
 
}private static 
function m4is_o8742(): array {
return ['affiliate' =>-3, 'company' =>-6, 'contact' =>-1, 'contactaction' =>-5, 'job' =>-9, 'productinterest' =>-4, 'recurringorder' =>-10 ];
 
}private static 
function m4is_j9368(): array {
return ['actionsequence' =>'Id,TemplateName,VisibleToTheseUsers', 'affiliate' =>'AffCode,AffName,ContactId,DefCommissionType,Id,LeadAmt,LeadCookieFor,LeadPercent,NotifyLead,NotifySale,ParentId,Password,PayoutType,SaleAmt,SalePercent,Status', 'campaign' =>'Id,Name,Status', 'campaignee' =>'Campaign,CampaignId,ContactId,Status', 'campaignstep' =>'CampaignId,Id,StepStatus,StepTitle,TemplateId', 'ccharge' =>'Amt,ApprCode,CCId,Id,MerchantId,OrderNum,PaymentId,RefNum', 'company' =>'AccountId,Address1Type,Address2Street1,Address2Street2,Address2Type,Address3Street1,Address3Street2,Address3Type,Anniversary,AssistantName,AssistantPhone,BillingInformation,Birthday,City,City2,City3,Company,CompanyID,ContactNotes,ContactType,Country,Country2,Country3,CreatedBy,DateCreated,Email,EmailAddress2,EmailAddress3,Fax1,Fax1Type,Fax2,Fax2Type,FirstName,Groups,Id,JobTitle,LastName,LastUpdated,LastUpdatedBy,Leadsource,LeadSourceId,MiddleName,Nickname,OwnerID,Password,Phone1,Phone1Ext,Phone1Type,Phone2,Phone2Ext,Phone2Type,Phone3,Phone3Ext,Phone3Type,Phone4,Phone4Ext,Phone4Type,Phone5,Phone5Ext,Phone5Type,PostalCode,PostalCode2,PostalCode3,ReferralCode,SpouseName,State,State2,State3,StreetAddress1,StreetAddress2,Suffix,Title,Username,Validated,Website,ZipFour1,ZipFour2,ZipFour3', 'contact' =>'AccountId,Address1Type,Address2Street1,Address2Street2,Address2Type,Address3Street1,Address3Street2,Address3Type,Anniversary,AssistantName,AssistantPhone,BillingInformation,Birthday,City,City2,City3,Company,CompanyID,ContactNotes,ContactType,Country,Country2,Country3,CreatedBy,DateCreated,Email,EmailAddress2,EmailAddress3,Fax1,Fax1Type,Fax2,Fax2Type,FirstName,Groups,Id,JobTitle,Language,LastName,LastUpdated,LastUpdatedBy,Leadsource,LeadSourceId,MiddleName,Nickname,OwnerID,Password,Phone1,Phone1Ext,Phone1Type,Phone2,Phone2Ext,Phone2Type,Phone3,Phone3Ext,Phone3Type,Phone4,Phone4Ext,Phone4Type,Phone5,Phone5Ext,Phone5Type,PostalCode,PostalCode2,PostalCode3,ReferralCode,SpouseName,State,State2,State3,StreetAddress1,StreetAddress2,Suffix,TimeZone,Title,Username,Validated,Website,ZipFour1,ZipFour2,ZipFour3', 'contactaction' =>'Accepted,ActionDate,ActionDescription,ActionType,CompletionDate,ContactId,CreatedBy,CreationDate,CreationNotes,EndDate,Id,IsAppointment,LastUpdated,LastUpdatedBy,OpportunityId,PopupDate,Priority,UserID', 'contactgroup' =>'GroupCategoryId,GroupDescription,GroupName,Id', 'contactgroupassign' =>'ContactGroup,ContactId,DateCreated,GroupId', 'contactgroupcategory' =>'CategoryDescription,CategoryName,Id', 'cprogram' =>'Active,BillingType,DefaultCycle,DefaultFrequency,DefaultPrice,Description,Family,HideInStore,Id,LargeImage,ProductId,ProgramName,ShortDescription,Sku,Status,Taxable', 'creditcard' =>'BillAddress1,BillAddress2,BillCity,BillCountry,BillName,BillState,BillZip,CardType,ContactId,Email,ExpirationMonth,ExpirationYear,FirstName,Id,Last4,LastName,MaestroIssueNumber,NameOnCard,PhoneNumber,ShipAddress1,ShipAddress2,ShipCity,ShipCompanyName,ShipCountry,ShipFirstName,ShipLastName,ShipMiddleName,ShipName,ShipPhoneNumber,ShipState,ShipZip,StartDateMonth,StartDateYear,Status',  'dataformfield' =>'DataType,DefaultValue,FormId,GroupId,Id,Label,ListRows,Name,Values', 'dataformgroup' =>'Id,Name,TabId', 'dataformtab' =>'Id,FormId,TabName', 'emailaddstatus' =>'DateCreated,Email,Id,LastClickDate,LastOpenDate,LastSentDate,Type', 'expense' =>'ContactId,DateIncurred,ExpenseAmt,ExpenseType,Id,TypeId', 'filebox' =>'ContactId,Extension,FileName,FileSize,Id,Public', 'groupassign' =>'Admin,GroupId,Id,UserId', 'invoice' =>'AffiliateId,ContactId,CreditStatus,DateCreated,Description,Id,InvoiceTotal,InvoiceType,JobId,LeadAffiliateId,PayPlanStatus,PayStatus,ProductSold,PromoCode,RefundStatus,Synced,TotalDue,TotalPaid', 'invoiceitem' =>'CommissionStatus,DateCreated,Description,Discount,Id,InvoiceAmt,InvoiceId,OrderItemId', 'invoicepayment' =>'Amt,Id,InvoiceId,PayDate,PaymentId,PayStatus,SkipCommission', 'job' =>'ContactId,DateCreated,DueDate,Id,JobNotes,JobRecurringId,JobStatus,JobTitle,OrderStatus,OrderType,ProductId,ShipCity,ShipCompany,ShipCountry,ShipFirstName,ShipLastName,ShipMiddleName,ShipPhone,ShipState,ShipStreet1,ShipStreet2,ShipZip,StartDate', 'jobrecurringinstance' =>'AutoCharge,DateCreated,Description,EndDate,Id,InvoiceItemId,RecurringId,StartDate,Status', 'lead' =>'AffiliateId,ContactID,CreatedBy,DateCreated,EstimatedCloseDate,Id,LastUpdated,LastUpdatedBy,Leadsource,NextActionDate,NextActionNotes,Objection,OpportunityNotes,OpportunityTitle,ProjectedRevenueHigh,ProjectedRevenueLow,StageID,StatusID,UserID', 'leadsource' =>'CostPerLead,Description,EndDate,Id,LeadSourceCategoryId,Medium,Message,Name,StartDate,Status,Vendor', 'leadsourcecategory' =>'Description,Id,Name', 'leadsourceexpense' =>'Amount,DateIncurred,Id,LeadSourceId,LeadSourceRecurringExpenseId,Notes,Title', 'leadsourcerecurringexpense' =>'Amount,EndDate,Id,LeadSourceId,NextExpenseDate,Notes,StartDate,Title', 'linkedcontacttype' =>'Id,MaxLinked,TypeName', 'orderitem' =>'CPU,Id,ItemDescription,ItemName,ItemType,Notes,OrderId,PPU,ProductId,Qty,SubscriptionPlanId', 'payment' =>'ChargeId,Commission,ContactId,Id,InvoiceId,PayAmt,PayDate,PayNote,PayType,RefundId,Synced,UserId', 'payplan' =>'AmtDue,DateDue,FirstPayAmt,Id,InitDate,InvoiceId,StartDate,Type', 'payplanitem' =>'AmtDue,AmtPaid,DateDue,Id,PayPlanId,Status', 'product' =>'BottomHTML,CityTaxable,CountryTaxable,Description,HideInStore,Id,InventoryLimit,InventoryNotifiee,IsPackage,LargeImage,NeedsDigitalDelivery,ProductName,ProductPrice,Shippable,ShippingTime,ShortDescription,Sku,StateTaxable,Status,Taxable,TopHTML,Weight', 'productcategory' =>'CategoryDisplayName,CategoryImage,CategoryOrder,Id,ParentId', 'productcategoryassign' =>'Id,ProductCategoryId,ProductId', 'productinterest' =>'DiscountPercent,Id,ObjectId,ObjType,ProductId,ProductType,Qty', 'productinterestbundle' =>'BundleName,Description,Id', 'recurringorder' =>'AffiliateId,AutoCharge,BillingAmt,BillingCycle,CC1,CC2,ContactId,EndDate,Frequency,Id,LastBillDate,LeadAffiliateId,MaxRetry,MerchantAccountId,NextBillDate,NumDaysBetweenRetry,OriginatingOrderId,PaidThruDate,ProductId,ProgramId,PromoCode,Qty,ReasonStopped,ShippingOptionId,StartDate,Status,SubscriptionPlanId', 'referral' =>'AffiliateId,ContactId,DateExpires,DateSet,Id,Info,IPAddress,Source,Type', 'savedfilter' =>'FilterName,Id,ReportStoredName,UserId', 'socialaccount' =>'Id,AccountName,AccountType,ContactId,DateCreated,LastUpdated', 'stage' =>'Id,StageName,StageOrder,TargetNumDays', 'stagemove' =>'CreatedBy,DateCreated,Id,MoveDate,MoveFromStage,MoveToStage,OpportunityId,PrevStageMoveDate,UserId', 'subscriptionplan' =>'Active,Cycle,Frequency,Id,PlanPrice,PreAuthorizeAmount,ProductId,Prorate', 'template' =>'Categories,Id,PieceTitle,PieceType', 'user' =>'City,Email,EmailAddress2,EmailAddress3,FirstName,GlobalUserId,HTMLSignature,Id,LastName,MiddleName,Nickname,Partner,Phone1,Phone1Ext,Phone1Type,Phone2,Phone2Ext,Phone2Type,PostalCode,Signature,SpouseName,State,StreetAddress1,StreetAddress2,Suffix,Title,ZipFour1', ];
 
} static 
function m4is_j4681(): bool {
$m4is_x39508 =self::$m4is_r1546->get_i2sdk_options();
 return isset($m4is_x39508['server_verified'])? (bool) $m4is_x39508['server_verified']: false;
 
}    static 
function m4is_f5248(string $m4is_v379, bool $m4is_v15639 =false, array $m4is_j108 =[]): array {
$m4is_v379 =strtolower(trim($m4is_v379 ));
 $m4is_m92735 =0;
 $m4is_v45136 =[];
 $m4is_a89 =array_key_exists($m4is_v379, self::$m4is_m810 )? array_filter(explode(',', self::$m4is_m810[$m4is_v379 ])): [];
 $m4is_m92735 =array_key_exists($m4is_v379, self::$m4is_u29516 )? self::$m4is_u29516[$m4is_v379 ]: 0;
 if($m4is_m92735 !== 0 ){
$m4is_a89 =array_merge($m4is_a89, m4is_s695::m4is_e654((int) $m4is_m92735 ));
 
} if(!empty($m4is_j108 )){
$m4is_a89 =array_diff($m4is_a89, $m4is_j108 );
 
}if($m4is_v15639 ){
if($m4is_v379 == 'contact' ){
$m4is_v45136 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'ignore_contact_fields', '' )));
 
}elseif($m4is_v379 == 'affiliate' ){
$m4is_v45136 =array_filter(explode(',', self::$m4is_r1546->m4is_j498('settings', 'ignore_affiliate_fields', '' )));
 
}$m4is_a89 =array_diff($m4is_a89, $m4is_v45136 );
 
}$m4is_a89 =array_filter($m4is_a89 );
 $m4is_a89 =apply_filters('memberium/infusionsoft/tables/fieldlist', $m4is_a89, $m4is_v379 );
 $m4is_a89 =apply_filters('memberium/keap/tables/fieldlist', $m4is_a89, $m4is_v379 );
 return array_values($m4is_a89 );
 
}static 
function m4is_n5367(int $m4is_w659 =-1 ): array {
global $wpdb;
  $m4is_a89 =[];
 if($m4is_a89 == -1 ){
$m4is_a89 =['Anniversary' =>13, 'Birthday' =>13, 'Email' =>5, 'EmailAddress2' =>5, 'EmailAddress3' =>5, ];
 
}$m4is_t06 =m4is_s695::m4is_w52();
 $m4is_v2613 ="SELECT concat('_', name) as `name`, `datatype` as `type` FROM `{$m4is_t06
}` WHERE `appname` =  %s AND formid = %d";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_r9613, (int) $m4is_w659 );
 $m4is_m615 =$wpdb->get_results($m4is_v2613, ARRAY_A );
 foreach($m4is_m615 as $m4is_g91703 ){
$m4is_a89[$m4is_g91703['name']]=$m4is_g91703['type'];
 
}if($m4is_w659 == -1 ){
$m4is_a89['Birthday']=13;
 $m4is_a89['Anniversary']=13;
 $m4is_a89['DateCreated']=14;
 $m4is_a89['LastUpdated']=14;
 
}return $m4is_a89;
 
}static 
function m4is_w6257(string $m4is_e80, string $m4is_s36520, string $m4is_g89163, int $m4is_u6183 ){
$m4is_s36520 =trim($m4is_s36520 );
 return self::$m4is_z59682->addCustomField($m4is_e80, $m4is_s36520, $m4is_g89163, $m4is_u6183 );
 
} static 
function m4is_z3902(int $m4is_h21895, string $m4is_v8723, string $m4is_s63 ='' ){
$m4is_v8723 =trim($m4is_v8723 );
 if(!empty($m4is_v8723 )&&!empty($m4is_h21895 )){
if(empty($m4is_s63 )){
$m4is_q87563 =preg_split("/[^[:alnum:]]/", $m4is_v8723 );
 if(count($m4is_q87563 )== 2 ){
$m4is_v8723 =trim($m4is_q87563[0]);
 $m4is_s63 =empty($m4is_q87563[1])? $m4is_s63 : trim($m4is_q87563[1]);
 
}
}$m4is_s63 =empty($m4is_s63 )? self::$m4is_r9613 : $m4is_s63;
 return self::$m4is_z59682->achieveGoal($m4is_s63, $m4is_v8723, $m4is_h21895 );
 
}
}    static 
function m4is_z64(string $m4is_e80, int $m4is_d07693, array $m4is_a89 ){
return self::$m4is_z59682->dsUpdate($m4is_e80, $m4is_d07693, $m4is_a89 );
 
} static 
function m4is_y7501(string $m4is_e80, array $m4is_a89 ){
return self::$m4is_z59682->dsAdd($m4is_e80, $m4is_a89 );
 
} static 
function m4is_o986(string $m4is_e80, int $m4is_h85 =0, int $m4is_d3012 =0, array $m4is_v76912 =[], array $m4is_a89 =[]){
$m4is_h85 =empty($m4is_h85 )? self::$m4is_z98 : $m4is_h85;
 if(empty($m4is_a89 )){
$m4is_a89 =m4is_c69807::m4is_f5248($m4is_e80, true );
 
}$m4is_n548 =self::$m4is_z59682->dsQuery($m4is_e80, $m4is_h85, $m4is_d3012, $m4is_v76912, $m4is_a89 );
 if(is_string($m4is_n548 )){
error_log('Memberium: [error]: Data Query API: ' . $m4is_e80 . "\n" . 'Return Fields: ' . implode(', ', $m4is_a89 ). "\n" . 'Query: ' . implode(', ', $m4is_v76912). "\n" . 'Page: ' . $m4is_d3012 . ' / Size: ' . $m4is_h85 . "\n" . ' - ' . $m4is_n548);
 $m4is_n548 =[];
 
}if(is_a($m4is_n548, 'WP_Error' )){
error_log('Memberium: [error] Data Query API: ' . $m4is_e80 . "\n" . 'Return Fields: ' . implode(', ', $m4is_a89 ). "\n" . 'Query: ' . implode(',', $m4is_v76912). "\n" . 'Page: ' . $m4is_d3012 . ' / Size: ' . $m4is_h85 . "\n" );
 $m4is_n548 =[];
 
}return $m4is_n548;
 
} static 
function m4is_i84(string $m4is_e80, int $m4is_h85 =0, int $m4is_d3012 =0, array $m4is_v76912 =[], array $m4is_a89 =[], string $m4is_o914 ='Id', bool $m4is_k236 =true){
$m4is_h85 =empty($m4is_h85 )? self::$m4is_z98 : $m4is_h85;
 if(empty($m4is_a89 )){
$m4is_a89 =self::m4is_f5248($m4is_e80, true );
 
}$m4is_n548 =self::$m4is_z59682->dsQueryOrderBy($m4is_e80, $m4is_h85, $m4is_d3012, $m4is_v76912, $m4is_a89, $m4is_o914, $m4is_k236 );
 if(is_string($m4is_n548 )){
error_log('Memberium: [error] Sorted Data Query API Error: ' . $m4is_e80 . "\n" . 'Return Fields: ' . implode(', ', $m4is_a89 ). "\n" . 'Query: ' . implode(',', $m4is_v76912). "\n" . 'Page: ' . $m4is_d3012 . ' / Size: ' . $m4is_h85 . "\n" . 'Sort By: ' . $m4is_o914 . ' / Ascending: ' . (int) $m4is_k236 . "\n" . ' - ' . $m4is_n548);
 $m4is_n548 =[];
 
}if(is_a($m4is_n548, 'WP_Error' )){
error_log('Memberium: [error] Sorted Data Query API Error: ' . $m4is_e80 . "\n" . 'Return Fields: ' . implode(', ', $m4is_a89 ). "\n" . 'Query: ' . implode(',', $m4is_v76912). "\n" . 'Page: ' . $m4is_d3012 . ' / Size: ' . $m4is_h85 . "\n" . 'Sort By: ' . $m4is_o914 . ' / Ascending: ' . (int) $m4is_k236 );
 $m4is_n548 =[];
 
}return $m4is_n548;
 
}  static 
function m4is_b6614(string $m4is_v379, int $m4is_d07693, array $m4is_a89 =[]): array {
$m4is_a89 =empty($m4is_a89 )? self::m4is_f5248($m4is_v379, false ): $m4is_a89;
 $m4is_j90523 =self::$m4is_z59682->dsLoad($m4is_v379, $m4is_d07693, $m4is_a89 );
 $m4is_j90523 =is_array($m4is_j90523 )? self::$m4is_r1546->m4is_f9708($m4is_j90523 ): $m4is_j90523;
 $m4is_j90523 =is_array($m4is_j90523 )? $m4is_j90523 : [];
 return $m4is_j90523;
 
}    static 
function m4is_x10656(string $m4is_j0361 ='%', string $m4is_v0626 ='' ){
global $wpdb;
 $m4is_v379 ='SavedFilter';
 $m4is_h3647 =self::m4is_f5248($m4is_v379, false );
 $m4is_v76912 =['ReportStoredName' =>$m4is_j0361 ];
 $m4is_k26689 =self::$m4is_z59682->dsQuery($m4is_v379, self::$m4is_z98, 0, $m4is_v76912, $m4is_h3647 );
 if(is_string($m4is_k26689 )){
error_log('Memberium:  Saved Search Sync API Error - ' . $m4is_k26689 );
 return [];
 
}if($m4is_j0361 == '%' &&is_array($m4is_k26689 )){
update_option('memberium_saved_searches', $m4is_k26689, false );
 
}return $m4is_k26689;
 
}    static 
function m4is_g46($m4is_j90523 ){
return is_string($m4is_j90523)&&stripos($m4is_j90523, '[RecordNotFound]' )!== false ;
 
} static 
function m4is_n146(array $m4is_m615, string $m4is_l9671 ='fieldname', string $m4is_v586 ='value' ): array {
$m4is_j90523 =[];
 foreach ($m4is_m615 as $m4is_g91703 ){
$m4is_j90523[$m4is_g91703[$m4is_l9671]]=$m4is_g91703[$m4is_v586];
 
}return $m4is_j90523;
 
} static 
function m4is_b86479(): string {
return 'Email';
 
} 
}

