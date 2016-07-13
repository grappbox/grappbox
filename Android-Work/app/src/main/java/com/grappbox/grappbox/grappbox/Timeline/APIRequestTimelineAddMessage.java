package com.grappbox.grappbox.grappbox.Timeline;

import android.app.Dialog;
import android.content.Context;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 17/02/2016.
 */
public class APIRequestTimelineAddMessage extends AsyncTask<String, Void, String> {

    private TimelineListFragment _context;
    private int _idTimeline;
    private Dialog _dialog;

    APIRequestTimelineAddMessage(TimelineListFragment context, int idTimeline, Dialog dialog)
    {
        _context = context;
        _idTimeline = idTimeline;
        _dialog = dialog;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null){
            APIRequestGetListMessageTimeline getApi = new APIRequestGetListMessageTimeline(_context, _idTimeline);
            getApi.execute();
            _dialog.dismiss();
        } else {
            Context context = _context.getContext();
            CharSequence text = "An error occured during the send message";
            Toast.makeText(context, text, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            APIConnectAdapter.getInstance().startConnection("timeline/postmessage/" + _idTimeline, "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("POST");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            JSONParam.put("token", SessionAdapter.getInstance().getToken());
            JSONParam.put("title", param[0]);
            JSONParam.put("message", param[1]);
            JSONData.put("data", JSONParam);
            Log.v("JSON", JSONData.toString());

            APIConnectAdapter.getInstance().sendJSON(JSONData);
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 201) {
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
