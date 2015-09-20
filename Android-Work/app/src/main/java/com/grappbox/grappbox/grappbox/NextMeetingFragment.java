package com.grappbox.grappbox.grappbox;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Arkanice on 18/09/2015.
 */
public class NextMeetingFragment   extends Fragment {

    private ListView _ListMeeting;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState)
    {
        View v;

        v = inflater.inflate(R.layout.fragment_next_meeting, container, false);
        _ListMeeting = (ListView)v.findViewById(R.id.list_next_meeting);
        ArrayList<HashMap<String, String>> listNextMeeting = new ArrayList<HashMap<String, String>>();

        HashMap<String, String> map = new HashMap<String, String>();
        map.put("meeting_subject", "Meeting Client");
        map.put("date_meeting", "21/2/2016");
        map.put("hour_meeting", "12h51");
        listNextMeeting.add(map);

        map = new HashMap<String, String>();
        map.put("meeting_subject", "Meeting Client");
        map.put("date_meeting", "21/2/2016");
        map.put("hour_meeting", "12h51");
        listNextMeeting.add(map);

        map = new HashMap<String, String>();
        map.put("meeting_subject", "Meeting Client");
        map.put("date_meeting", "21/2/2016");
        map.put("hour_meeting", "12h51");
        listNextMeeting.add(map);

        SimpleAdapter meetingAdapter = new SimpleAdapter(v.getContext(), listNextMeeting, R.layout.next_meeting_item,
                new String[] {"meeting_subject", "date_meeting", "hour_meeting"}, new int[] {R.id.meeting_subject, R.id.date_meeting, R.id.hour_meeting});
        _ListMeeting.setAdapter(meetingAdapter);
        return v;
    }
}
