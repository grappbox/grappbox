package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.R;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;


public class NextMeetingFragment extends Fragment {

    private View _view;
    private List<ContentValues> _value = null;

    @Override
    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_next_meeting, container, false);
        if (_value == null) {
            APIRequestNextMeeting api = new APIRequestNextMeeting();
            api.execute();
        } else {
            createContentView(_value);
        }
        return _view;
    }

    public void createContentView(List<ContentValues> contentValues)
    {
        ListView TeamList = (ListView)_view.findViewById(R.id.list_next_meeting);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();

        _value = contentValues;
        for (ContentValues item : contentValues){
            HashMap<String, String> map = new HashMap<String, String>();
            map.put("meeting_title", item.get("projects_name").toString() + " " + item.get("title").toString());
            map.put("meeting_subject", item.get("description").toString());
            map.put("date_meeting_start", item.get("begin_date").toString());
            map.put("date_meeting_end", item.get("end_date").toString());
            map.put("logo_project_image_meeting", String.valueOf(R.mipmap.icon_launcher));
            listMemberTeam.add(map);
        }

        SimpleAdapter meetingAdapter = new SimpleAdapter(_view.getContext(), listMemberTeam, R.layout.item_next_meeting,
                new String[] {"meeting_title", "meeting_subject", "date_meeting_start", "date_meeting_end", "logo_project_image_meeting"},
                new int[] {R.id.meeting_title, R.id.meeting_subject, R.id.date_meeting_start,  R.id.date_meeting_end, R.id.logo_project_image_metting});
        TeamList.setAdapter(meetingAdapter);
    }

    public class APIRequestNextMeeting extends AsyncTask<String, Void, List<ContentValues>> {

        @Override
        protected void onPostExecute(List<ContentValues> result)
        {
            super.onPostExecute(result);
            if (result != null)
                createContentView(result);
        }

        @Override
        protected List<ContentValues> doInBackground(String ... param)
        {
            String resultAPI;
            Integer APIResponse;
            List<ContentValues> listResult = null;

            try {
                APIConnectAdapter.getInstance().startConnection("dashboard/getnextmeetings/" + SessionAdapter.getInstance().getToken(), "V0.2");
                APIConnectAdapter.getInstance().setRequestConnection("GET");
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("Response Meeting", String.valueOf(APIResponse));
                if (APIResponse == 200) {
                    Log.v("JSON Meeting", resultAPI);
                    listResult = APIConnectAdapter.getInstance().getListNextMeeting(resultAPI);
                }

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            }finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return listResult;
        }

    }
}
