package com.grappbox.grappbox.grappbox.Timeline;

import android.app.Dialog;
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
 * Created by tan_f on 17/02/2016.
 */
public class APIRequestTimelineEditMessage  extends AsyncTask<String, Void, String> {

    private TimelineMessage _context;
    private int _idTimeline;
    private Dialog _dialog;
    private boolean _isComment = false;
    private int _idMessage;

    APIRequestTimelineEditMessage(TimelineMessage context, int idTimeline, Dialog dialog)
    {
        _context = context;
        _idTimeline = idTimeline;
        _dialog = dialog;
        _isComment = false;
    }

    APIRequestTimelineEditMessage(TimelineMessage context, int idTimeline, int idMessage, Dialog dialog)
    {
        _context = context;
        _idTimeline = idTimeline;
        _idMessage = idMessage;
        _dialog = dialog;
        _isComment = true;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null){
            if (_isComment) {
                APIRequestGetMessageComment apiGet = new APIRequestGetMessageComment(_context, _idTimeline, _idMessage);
                apiGet.execute();
            } else {
                APIRequestGetListMessageTimeline apiGet = new APIRequestGetListMessageTimeline(_context, _idTimeline);
                apiGet.execute();
            }
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
            APIConnectAdapter.getInstance().startConnection("timeline/editmessage/" + _idTimeline, "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("PUT");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            JSONParam.put("token", SessionAdapter.getInstance().getToken());
            JSONParam.put("messageId", Integer.parseInt(param[0]));
            JSONParam.put("title", param[1]);
            JSONParam.put("message", param[2]);
            JSONData.put("data", JSONParam);
            Log.v("JSON", JSONData.toString());

            APIConnectAdapter.getInstance().sendJSON(JSONData);
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 201 || APIResponse == 200) {
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
