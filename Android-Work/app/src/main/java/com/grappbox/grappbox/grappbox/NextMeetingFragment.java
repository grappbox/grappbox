package com.grappbox.grappbox.grappbox;

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

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Vector;


public class NextMeetingFragment extends Fragment {

    ListView _TeamList;
    View _view;
    List<ContentValues> _value = null;

    public NextMeetingFragment() {
        // Required empty public constructor
    }

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
            APIRequestLogin api = new APIRequestLogin();
            api.execute();
        } else {
            createContentView(_value);
        }
        return _view;
    }

    public void createContentView(List<ContentValues> contentValues)
    {
        _TeamList = (ListView)_view.findViewById(R.id.list_next_meeting);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();

        _value = contentValues;
        for (ContentValues item : contentValues){
            HashMap<String, String> map = new HashMap<String, String>();
            map.put("meeting_title", item.get("project_name").toString() + " " + item.get("event_title").toString());
            map.put("meeting_subject", item.get("event_description").toString());
            map.put("date_meeting_start", item.get("event_begin_date").toString());
            map.put("place_meeting_start", item.get("event_begin_place").toString());
            map.put("date_meeting_end", item.get("event_end_date").toString());
            map.put("place_meeting_end", item.get("event_end_place").toString());
            map.put("logo_project_image_metting", String.valueOf(R.mipmap.icon_launcher));
            listMemberTeam.add(map);
        }

        SimpleAdapter meetingAdapter = new SimpleAdapter(_view.getContext(), listMemberTeam, R.layout.item_next_meeting,
                new String[] {"meeting_title", "meeting_subject", "date_meeting_start", "place_meeting_start", "date_meeting_end", "place_meeting_end", "logo_project_image_metting"},
                new int[] {R.id.meeting_title, R.id.meeting_subject, R.id.date_meeting_start, R.id.place_meeting_start, R.id.date_meeting_end, R.id.place_meeting_end, R.id.logo_project_image_metting});
        _TeamList.setAdapter(meetingAdapter);
    }

    public class APIRequestLogin extends AsyncTask<String, Void, List<ContentValues>> {

        private static final String _API_URL_BASE = "http://api.grappbox.com/app_dev.php/";

        @Override
        protected void onPostExecute(List<ContentValues> result)
        {
            super.onPostExecute(result);
            if (result != null)
                createContentView(result);
        }

        private List<ContentValues> getNextMeeting(String result)  throws JSONException
        {
            final String[] DATA_MEETING = {"project_name", "project_logo", "event_type", "event_title", "event_description", "event_begin_date", "event_end_date"};
            final String[] DATA_DATE_EVENT = {"date", "timezone"};
            final String[] KEY_MEETING = {"project_name", "project_logo", "event_type", "event_title", "event_description", "event_begin_date", "event_begin_place", "event_end_date", "event_end_place"};


            JSONObject forecastJSON = new JSONObject(result);
            List<ContentValues> list = new Vector<ContentValues>();
            int i = 0;
            while (1 == 1) {
                String person = "Meeting " + String.valueOf(i);
                if (!forecastJSON.has(person) || forecastJSON.getString(person).length() == 0)
                    break;
                ContentValues values = new ContentValues();
                for (int data = 0; data < 5; ++data) {
                    if (forecastJSON.getJSONObject(person).getString(DATA_MEETING[data]) == null)
                        values.put(KEY_MEETING[data], "");
                    else
                        values.put(KEY_MEETING[data], forecastJSON.getJSONObject(person).getString(DATA_MEETING[data]));
                }
                values.put(KEY_MEETING[5], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[5]).getString(DATA_DATE_EVENT[0]));
                values.put(KEY_MEETING[6], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[5]).getString(DATA_DATE_EVENT[1]));
                values.put(KEY_MEETING[7], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[6]).getString(DATA_DATE_EVENT[0]));
                values.put(KEY_MEETING[8], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[6]).getString(DATA_DATE_EVENT[1]));

                list.add(values);
                ++i;
            }
            return list;
        }

        @Override
        protected List<ContentValues> doInBackground(String ... param)
        {
            String resultAPI;
            List<ContentValues> listResult = null;

            try {
                APIConnectAdapter.getInstance().startConnection("dashboard/getnextmeetings/" + SessionAdapter.getInstance().getToken());
                APIConnectAdapter.getInstance().setRequestConnection("GET");
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                listResult = getNextMeeting(resultAPI);

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
