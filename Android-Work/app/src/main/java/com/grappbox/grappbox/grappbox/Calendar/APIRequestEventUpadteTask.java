package com.grappbox.grappbox.grappbox.Calendar;

import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 21/01/2016.
 */
public class APIRequestEventUpadteTask extends AsyncTask<String, Void, String> {

    private EventDetailFragment _context;
    private int _idEvent;

    APIRequestEventUpadteTask(EventDetailFragment context, int idEvent)
    {
        _context = context;
        _idEvent = idEvent;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null) {
            CharSequence text = "Event information correctly update";
            Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
        } else {
            CharSequence text = "An Error Occured.";
            Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
        }
        APIRequestGetEventData refresh = new APIRequestGetEventData(_context, _idEvent);
        refresh.execute();
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection("event/editevent", "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("PUT");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            JSONParam.put("token", SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
            JSONParam.put("eventId", _idEvent);
            if (!param[0].equals("-1")) {
                JSONParam.put("projectId", Integer.parseInt(param[0]));
            }
            JSONParam.put("title", param[1]);
            JSONParam.put("description", param[2]);
            JSONParam.put("icon", param[3]);
            JSONParam.put("typeId", 1);
            JSONParam.put("begin", param[4]);
            JSONParam.put("end", param[5]);
            JSONData.put("data", JSONParam);
            Log.v("JSON", JSONData.toString());

            APIConnectAdapter.getInstance().sendJSON(JSONData);
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            } else {
                return null;
            }

        } catch (IOException | JSONException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}