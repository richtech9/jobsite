/*





 */

/*
There are two tables for societies/groups. Even though societies really are groups over 100, the tables will have the society word in them

 1) table wp_societies has information that is for the entire society: the name, the society page, the owner, and overall stats and are constantly modified from elsewhere
      groups that are not large enough to be a society will simply have a lot of this data blank

  Every user can be a member in  no more than one society, this can be stored in the user lookup table.
  A mentor can run a different group, that reference is stored in the wp_societies table
  A mentor can never be a member of his own group
  A mentor cannot be a member of any groups run by users they mentor, not directly, and not indirectly

 2) table wp_society_periods has a row for each society/month/user. The current period does not have an ending date
       The current period row holds current stats as well as current achievements, as well as the running amount earned, for  each member and mentor
       All percentages and amount earned is stored here as a running total
       Achievements, such as selling or recruiting amounts, that unlock higher percentages, are stored here also

       When the period ends, this data is kept, but is now historical data, and is not used in the current data, which is stored in the new period row


 */