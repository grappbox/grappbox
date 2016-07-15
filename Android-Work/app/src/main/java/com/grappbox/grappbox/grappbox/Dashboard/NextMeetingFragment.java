package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
import com.grappbox.grappbox.grappbox.R;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.List;


public class NextMeetingFragment extends LoadingFragment {

    private View _view;
    private NextMeetingFragment _context = this;
    private List<ContentValues> _value = null;
    private SwipeRefreshLayout _swiper;
    public SwipeRefreshLayout.OnRefreshListener _refresher;

    @Override
    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_next_meeting, container, false);
        _swiper = (SwipeRefreshLayout) _view.findViewById(R.id.pull_refresher);
        if (_value == null) {
            APIRequestNextMeeting api = new APIRequestNextMeeting(this);
            api.execute();
        } else {
            createContentView(_value);
        }
        _refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                APIRequestNextMeeting api = new APIRequestNextMeeting(_context);
                api.SetRefreshSwiper(_swiper);
                api.execute();
            }
        };
        _swiper.setOnRefreshListener(_refresher);
        return _view;
    }

    public void createContentView(List<ContentValues> contentValues)
    {
        final SimpleDateFormat dateformat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        final SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        final SimpleDateFormat dayFormat = new SimpleDateFormat("yyyy-MM-dd");

        ListView TeamList = (ListView)_view.findViewById(R.id.list_next_meeting);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();

        _value = contentValues;
        for (ContentValues item : contentValues){
            Calendar dateMeeting = Calendar.getInstance();
            HashMap<String, String> map = new HashMap<String, String>();

            map.put("meeting_title", item.get("projects_name").toString() + " " + item.get("title").toString());
            map.put("meeting_subject", item.get("description").toString());
            try {
                dateMeeting.setTime(dateformat.parse(item.get("begin_date").toString()));

                map.put("date_meeting_start", dayFormat.format(dateMeeting.getTime()));
                map.put("date_meeting_start_hour", hourFormat.format(dateMeeting.getTime()));

                dateMeeting.setTime(dateformat.parse(item.get("end_date").toString()));

                map.put("date_meeting_end", dayFormat.format(dateMeeting.getTime()));
                map.put("date_meeting_end_hour", hourFormat.format(dateMeeting.getTime()));

            } catch (ParseException p) {
                Log.e("Date parse", "Parsing error");
            }

            map.put("logo_project_image_meeting", String.valueOf(R.mipmap.icon_launcher));
            listMemberTeam.add(map);
        }

        SimpleAdapter meetingAdapter = new SimpleAdapter(_view.getContext(), listMemberTeam, R.layout.item_next_meeting,
                new String[] {"meeting_title", "meeting_subject", "date_meeting_start", "date_meeting_start_hour", "date_meeting_end", "date_meeting_end_hour", "logo_project_image_meeting"},
                new int[] {R.id.meeting_title, R.id.meeting_subject, R.id.date_meeting_start, R.id.date_meeting_start_hour, R.id.date_meeting_end, R.id.date_meeting_end_hour, R.id.logo_project_image_metting});
        TeamList.setAdapter(meetingAdapter);
    }

}
