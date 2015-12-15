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

public class TeamOccupationFragment extends Fragment {

    private ListView _TeamList;
    private List<ContentValues> _value = null;
    private View    _view;

    @Override
    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_team_occupation, container, false);
        if (_value != null) {
            createContentView(_value);
        } else {
            APIRequestTeamOccupation api = new APIRequestTeamOccupation();
            api.execute();
        }
        return _view;
    }

    public void createContentView(List<ContentValues> contentValues)
    {
        _value = contentValues;
        _TeamList = (ListView)_view.findViewById(R.id.list_team_occupation);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();

        for (ContentValues item : contentValues){
            HashMap<String, String> map = new HashMap<String, String>();
            map.put("name_member", item.get("first_name").toString() + " " + item.get("last_name").toString());
            map.put("occupation_state", item.get("occupation").toString());
            map.put("occupation_project_name", item.get("project_name").toString());
            map.put("profil_image", String.valueOf(R.mipmap.icon_launcher));
            listMemberTeam.add(map);
        }

        SimpleAdapter teamAdapter = new SimpleAdapter(_view.getContext(), listMemberTeam, R.layout.item_team_occupation,
                new String[] {"profil_image", "name_member", "occupation_state", "occupation_project_name"}, new int[] {R.id.profil_image, R.id.name_member, R.id.occupation_state, R.id.occupation_project_name});
        _TeamList.setAdapter(teamAdapter);
    }

    public class APIRequestTeamOccupation extends AsyncTask<String, Void, List<ContentValues>> {

        private static final String _API_URL_BASE = "http://api.grappbox.com/app_dev.php/";

        private List<ContentValues> getTeamOccupation(String result) throws JSONException
        {
            final String[] DATA_TEAM = {"project_name", "user_id", "first_name", "last_name", "occupation", "number_of_tasks_begun", "number_of_ongoing_tasks"};

            if (result.length() == 0 || result.equals("[]"))
                return null;
            JSONObject forecastJSON = new JSONObject(result);
            List<ContentValues> list = new Vector<ContentValues>();
            int i = 0;
            while (1 == 1) {
                String person = "Person " + String.valueOf(i);
                if (!forecastJSON.has(person) || forecastJSON.getString(person).length() == 0)
                    break;
                ContentValues values = new ContentValues();
                for (int data = 0; data < DATA_TEAM.length; ++data){
                    values.put(DATA_TEAM[data], forecastJSON.getJSONObject(person).getString(DATA_TEAM[data]));
                }
                list.add(values);
                ++i;
            }
            return list;
        }

        @Override
        protected void onPostExecute(List<ContentValues> result) {
            super.onPostExecute(result);
            if (result != null)
                createContentView(result);
        }

        @Override
        protected List<ContentValues> doInBackground(String ... param)
        {
            String resultAPI;
            List<ContentValues> listResult = null;

            try {
                APIConnectAdapter.getInstance().startConnection("dashboard/getteamoccupation/" + SessionAdapter.getInstance().getToken());
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                listResult =  getTeamOccupation(resultAPI);

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return listResult;
        }

    }
}
