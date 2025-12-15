# Tournament Bracket System - UI Description

## Bracket View (`tournament-bracket.php`)

### Header Section
- **Back Button** (←) - Returns to manage tournaments page
- **Tournament Name** - Displays "Bracket - [Tournament Name]"
- **Generate Bracket Button** - Appears when no bracket exists yet (Cyan-Purple gradient button)

### Bracket Display

The bracket is displayed horizontally with rounds shown from left to right:

```
[Round 1]         [Quarter-Finals]    [Semi-Finals]        [Finals]
┌──────────┐      ┌──────────┐        ┌──────────┐         ┌──────────┐
│ Match 1  │      │ Match 1  │        │ Match 1  │         │ Match 1  │
├──────────┤      ├──────────┤        ├──────────┤         ├──────────┤
│ Player 1 │──┐   │   TBD    │──┐     │   TBD    │──┐      │   TBD    │
│    VS    │  │   │    VS    │  │     │    VS    │  │      │    VS    │
│ Player 2 │  │   │   TBD    │  │     │   TBD    │  │      │   TBD    │
└──────────┘  │   └──────────┘  │     └──────────┘  │      └──────────┘
              │                 │                   │
┌──────────┐  │   ┌──────────┐  │     ┌──────────┐  │
│ Match 2  │  │   │ Match 2  │  │     │ Match 2  │  │
├──────────┤  │   ├──────────┤  │     ├──────────┤  │
│ Player 3 │──┘   │   TBD    │──┘     │   TBD    │──┘
│    VS    │      │    VS    │        │    VS    │
│ Player 4 │      │   TBD    │        │   TBD    │
└──────────┘      └──────────┘        └──────────┘
```

### Match Cards

Each match card displays:
- **Round Label** (top-left corner): "Match 1", "Match 2", etc.
- **Participant 1** (draggable card with player/team name)
- **VS Divider** (centered between participants)
- **Participant 2** (draggable card with player/team name)
- **Winner Indicator** (green background when completed)

### Visual States

1. **Pending Match** (Gray):
   - Border: Gray (#374151)
   - Background: Dark gray (#1f2937)
   - Participants: Light gray background

2. **Completed Match** (Green):
   - Border: Green (#10b981)
   - Winner card: Dark green background (#065f46) with green border
   - Loser card: Normal gray background

3. **BYE Match** (Faded):
   - Semi-transparent (50% opacity)
   - Single participant auto-advances

4. **Empty Slot** (TBD):
   - Dashed border
   - Gray text
   - Not draggable

### Drag and Drop Interaction

1. **Drag Start**: Participant card shows "grabbing" cursor and becomes semi-transparent
2. **Drag Over**: Target area gets cyan border highlight
3. **Drop**: Winner is set, match turns green, winner advances to next round

### Features Implemented

✅ **Visual Bracket Structure**: Complete tournament bracket with all rounds
✅ **Drag and Drop**: Intuitive winner selection by dragging participants
✅ **Auto-Advancement**: Winners automatically populate next round matches
✅ **Round Naming**: Finals, Semi-Finals, Quarter-Finals, Round 1, Round 2, etc.
✅ **Color Coding**: Green for completed, gray for pending
✅ **Team Support**: Works with both players and teams
✅ **BYE Handling**: Automatic advancement for uneven participant counts
✅ **Real-time Updates**: Bracket refreshes after setting winners
✅ **Toast Notifications**: Success/error messages for all actions
✅ **Responsive Design**: Horizontal scroll for large brackets

### User Instructions (Displayed on Page)

"How to use:
• Drag a participant from a match and drop it onto the winner slot to advance them
• Winners automatically advance to the next round
• Green matches are completed, gray matches are pending"

### Color Scheme (Dark Neon Theme)

- Background: Dark gray (#1f2937, #374151)
- Borders: Gray/Cyan/Green
- Text: White/Cyan/Gray
- Accents: Cyan (#06b6d4) and Purple (#9333ea)
- Success: Green (#10b981)
- Winner highlight: Dark green (#065f46)

### Technical Details

- **Draggable API**: HTML5 native drag and drop
- **Dynamic Rendering**: JavaScript-generated bracket structure
- **AJAX Updates**: Real-time API calls without page refresh
- **Validation**: Server-side winner validation
- **Ownership**: Only organizer/admin can manage bracket
