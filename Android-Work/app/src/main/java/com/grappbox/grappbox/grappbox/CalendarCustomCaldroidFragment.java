package com.grappbox.grappbox.grappbox;

import com.roomorama.caldroid.CaldroidFragment;
import com.roomorama.caldroid.CaldroidGridAdapter;

public class CalendarCustomCaldroidFragment extends CaldroidFragment {
    @Override
    public CaldroidGridAdapter getNewDatesGridAdapter(int month, int year) {
        // TODO Auto-generated method stub
        return new CalendarCaldroidCustomAdapter(getActivity(), month, year,
                getCaldroidData(), extraData);
    }
}
