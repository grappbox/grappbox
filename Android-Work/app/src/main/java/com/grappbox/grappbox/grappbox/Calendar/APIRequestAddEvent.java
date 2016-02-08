package com.grappbox.grappbox.grappbox.Calendar;

import android.content.Context;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 31/01/2016.
 */
public class APIRequestAddEvent extends AsyncTask<String, Void, String> {

    private AddEventFragment _context;

    APIRequestAddEvent(AddEventFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null){
            AgendaFragment agendaFragment = new AgendaFragment();
            _context.getFragmentManager().beginTransaction().replace(R.id.content_frame, agendaFragment).commit();
        } else {
            Context context = _context.getContext();
            CharSequence text = "An error occured during the event creation";
            Toast.makeText(context, text, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection("event/postevent", "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("POST");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            JSONParam.put("token", SessionAdapter.getInstance().getToken());
            if (!param[0].equals("-1")) {
                JSONParam.put("projectId", Integer.parseInt(param[0]));
            }
            JSONParam.put("title", param[1]);
            JSONParam.put("description", param[2]);
            JSONParam.put("icon", "");
            JSONParam.put("typeId", 1);
            JSONParam.put("begin", param[3]);
            JSONParam.put("end", param[4]);
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

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } catch (JSONException j) {
            Log.e("JSON", "Error ", j);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }
}
