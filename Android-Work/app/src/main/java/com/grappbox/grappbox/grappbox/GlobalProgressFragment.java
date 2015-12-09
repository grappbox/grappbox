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


public class GlobalProgressFragment extends Fragment {

    View _view;
    ListView _projectList;

    public GlobalProgressFragment() {
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
        _view = inflater.inflate(R.layout.fragment_global_progress, container, false);
        APIRequestTeamOccupation api = new APIRequestTeamOccupation();
        api.execute();
        return _view;
    }

    private String getValueContennt(String item)
    {
        final String notSpecified = getResources().getString(R.string.data_not_specified);
        if (item.equals("null") || item.length() == 0)
        {
            return notSpecified;
        }
        return item;
    }

    private void createContentView(List<ContentValues> values)
    {
        _projectList = (ListView)_view.findViewById(R.id.list_global_progress);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();

        for (ContentValues item : values){
            HashMap<String, String> map = new HashMap<String, String>();
            map.put("project_name", getValueContennt(item.get("project_name").toString()));
            map.put("project_description", "Description : " + getValueContennt(item.get("project_description").toString()));
            map.put("client_telephone_contact", "Contact Phone : " + getValueContennt(item.get("project_phone").toString()));
            map.put("client_company", getValueContennt(item.get("project_company").toString()));
            map.put("client_contact_mail", "Mail : " + getValueContennt(item.get("contact_mail").toString()));
            map.put("client_contact_facebook", "Facebook : " + getValueContennt(item.get("facebook").toString()));
            map.put("client_contact_twitter", "Twitter : " + getValueContennt(item.get("twitter").toString()));
            map.put("project_image", String.valueOf(R.mipmap.icon_launcher));
            listMemberTeam.add(map);
        }

        SimpleAdapter teamAdapter = new SimpleAdapter(_view.getContext(), listMemberTeam, R.layout.item_global_progress,
                new String[] {"project_image", "project_name", "project_description", "client_telephone_contact", "client_company", "client_contact_mail", "client_contact_facebook", "client_contact_twitter"},
                new int[] {R.id.project_image, R.id.project_name, R.id.project_description, R.id.client_telephone_contact, R.id.client_company, R.id.client_contact_mail, R.id.client_contact_facebook, R.id.client_contact_twitter});
        _projectList.setAdapter(teamAdapter);
    }

    public class APIRequestTeamOccupation extends AsyncTask<String, Void, List<ContentValues>> {

        private static final String _API_URL_BASE = "http://api.grappbox.com/app_dev.php/";

        private List<ContentValues> getTeamOccupation(String result) throws JSONException
        {
            final String[] DATA_PROGRESS = {
                    "project_id",
                    "project_name",
                    "project_description",
                    "project_phone",
                    "project_company",
                    "project_logo",
                    "contact_mail",
                    "facebook",
                    "twitter",
                    "number_finished_tasks",
                    "number_ongoing_tasks",
                    "number_tasks",
                    "number_bugs",
                    "number_messages"};

            if (result.length() == 0 || result.equals("[]"))
                return null;
            JSONObject forecastJSON = new JSONObject(result);
            List<ContentValues> list = new Vector<ContentValues>();
            int i = 0;
            while (1 == 1) {
                String project = "Project " + String.valueOf(i);
                if (!forecastJSON.has(project) || forecastJSON.getString(project).length() == 0)
                    break;
                ContentValues values = new ContentValues();
                for (int data = 0; data < DATA_PROGRESS.length; ++data){
                    if (forecastJSON.getJSONObject(project).getString(DATA_PROGRESS[data]) == null)
                        values.put(DATA_PROGRESS[data], "");
                    else
                        values.put(DATA_PROGRESS[data], forecastJSON.getJSONObject(project).getString(DATA_PROGRESS[data]));
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
            List<ContentValues> contentAPI = null;
            String resultAPI;

            try {
                APIConnectAdapter.getInstance().startConnection("dashboard/getprojectsglobalprogress/" + SessionAdapter.getInstance().getToken());
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                contentAPI = getTeamOccupation(resultAPI);

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            }finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return contentAPI;
        }

    }
}
