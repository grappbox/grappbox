package com.grappbox.grappbox.grappbox.Timeline;

import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;


import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 17/02/2016.
 */
public class APIRequestGetTimeline extends AsyncTask<String, Void, String> {

    private TimelineFragment _context;
    private int _idProject;
    private Integer _APIRespond;

    APIRequestGetTimeline(TimelineFragment context, int idProject)
    {
        _context = context;
        _idProject = idProject;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);

        if (result == null) {
            switch (_APIRespond){
                case 206:
                    CharSequence text = "No timeline exist for this project";
                    Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
                    break;

                default:
                    break;
            }
            return;
        }
        try
        {
            JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
            JSONArray TimelineList = forecastJSON.getJSONArray("array");

            for (int i = 0; i < TimelineList.length(); ++i)
            {
                JSONObject TimelineJSON = TimelineList.getJSONObject(i);
                if (TimelineJSON.getString("typeId").equals("1")) {
                    _context.fillContentCustomer(Integer.valueOf(TimelineJSON.getString("id")));
                } else if (TimelineJSON.getString("typeId").equals("2")) {
                    _context.fillContentIntern(Integer.valueOf(TimelineJSON.getString("id")));
                }
            }
        } catch (JSONException j){
            j.printStackTrace();
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;

        try {
            String token = SessionAdapter.getInstance().getToken();
            APIConnectAdapter.getInstance().startConnection("timeline/gettimelines/" + token + "/" + String.valueOf(_idProject), "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            _APIRespond = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", _APIRespond.toString());
            if (_APIRespond == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            } else {
                return null;
            }

        } catch (IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
