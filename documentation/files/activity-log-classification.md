# Classification (Categories and Actions)
[Return to TOC](../README.md)

One challenge with consistent classification is POV ( point of view ). The same baptism can be reported
from the point of view of the spiritual journey of the baptized person, or the spiritual journey of 
the leader who has baptized someone. In traditional church with paid leadership, you expect the leader
to baptize as part of their job description, so the natural point of celebration is the individual who 
got baptized, but in a movement of non-professional followers of Jesus, whereas the one who is baptized 
is a blessing to celebrate, but the one who has been faithful to multiply their faith is a greater blessing 
to celebrate. So what POV do you report in the movement_log? The baptizee, the baptizer, or both?

The action and category are not directly type and subtype in relationship. The primary programming strategy is 
as a key/value filter, but, as following the Google Analytics event system, I've added suggested categories, 
but the list can be filtered and new categories can be added, and messages can be modified. I would discourage the 
changing of actions because there are system dependencies on their keys, but they can also be changed if needed.

-----

| Action | Category | Note | Trigger |
| --- | --- | --- | --- |
|--- | outreach | ---- |  ----|
| studying* | | XX is studying "_____" | website content trigger
| more_info* | | XX is reaching out to learn more about Jesus | website content trigger: lead form
| new_contact | | XX is taking their next step towards Jesus | contact: add new contact
| new_contact_{source} | | XX is tracking a new contact for followup | contact: add new contact + source
--- | follow_up | | ---- | ----
| quick_button_no_answer | | XX is trying to get a hold of a new contact. Pray for connection. | contact: quick action: no answer
| quick_button_meeting_complete | | XX met for the first time with a new contact | contact: seeker path: first meeting complete
| quick_button_no_show | | XX missed a meeting with a disciple-maker. Pray for better responsiveness. | contact: quick action: meeting no show
| milestone_belief | | XX has declared faith in Jesus | contact: milestone: states belief
| milestone_sharing | | XX is actively sharing their faith in Jesus | contact: milestone: sharing faith
| milestone_baptizing | | XX is actively baptizing others in obedience to Jesus | contact: milestone: baptizing
| ---| training | ---- |  |
| new_group | | XX is reporting a new group has formed | group: add new : grouptype: group
| new_church | | XX is reporting a new church has formed | group: add new : grouptype: church
| new_lead_team | | XX is reporting new leadership team has formed | group: add new : grouptype: team
| new_multiplier | | XX added a new disciple maker to the team | user: add user: role of multiplier, responder
| milestone_planting | | XX is actively planting churches | contact: milestone: baptizing
---| multiplying | ---- | ---- |
| milestone_baptized | | XX is reporting a 4th generation baptism | contact: milestone: baptized + baptized by
| milestone_baptized | | XX baptized their first person! | contact: milestone: baptized + baptized by
| milestone_baptized | | XX reported a new baptism | contact: milestone: baptized + baptized by
| disciple_generation | | XX is reporting new generations of disciples | contact: coached by or baptized by added.
| group_generation | | XX is reporting new 4th generation group | group: child group added that has type group
| church_generation | | XX is reporting new 3rd generations church | group: child group added that has type church
---| barriers | ----| |
| closed_martyred | | XX has been martyred for the gospel. | contact: status reason
| closed_apologetics | | XX only wants to argue or debate. Pray for humility. | contact: status reason
| closed_no_longer_responding | | XX is no longer spiritually interested. Pray for conviction. | contact: status reason
| closed_no_longer_interested | | XX is no longer responding. Pray for conviction. | contact: status reason
| closed_hostile_self_gain | | XX is hostile and playing games with the disciple maker. Pray for conviction. | contact: status reason
| closed_moved | | XX has moved away. Pray for spiritual health in new location. | contact: status reason
| paused_not_responding | | XX is not responding. Pray for responsiveness. | contact: status reason
| paused_not_available | | XX is not available to meet. Pray for connection. | contact: status reason
| paused_little_interest | | XX shows little spiritual interest/hunger. Pray for conviction. | contact: status reason
| paused_no_initiative | | XX shows no initiative. Pray for conviction. | contact: status reason
| paused_questionable_motives | | XX has questionable motives. Pray for conviction and discernment. | contact: status reason
| paused_ball_in_their_court | | XX has been spiritually challenged. Pray for spiritual responsiveness. | contact: status reason
| paused_wait_and_see | | XX has been spiritually challenged. Pray for spiritual responsiveness. | contact: status reason
| inactive_church | | XX has disbanded as a church | group: status + group type
| inactive_group | | XX has disbanded as a group | group: status + group type

