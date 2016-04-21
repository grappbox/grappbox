package com.grappbox.grappbox.grappbox.Timeline;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 22/02/2016.
 */
public class APIRequestGetMessageComment extends AsyncTask<String, Void, String> {

    private TimelineMessage _context;
    private int _idTimeline;
    private int _idMessage;
    private Integer _APIRespond;

    APIRequestGetMessageComment(TimelineMessage context, int idTimeline, int idMessage)
    {
        _context = context;
        _idTimeline = idTimeline;
        _idMessage = idMessage;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);

        if (result == null) {
            return;
        }
        try
        {
            List<ContentValues> listMessage = new Vector<ContentValues>();
            JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
            JSONArray TimelineMessage = forecastJSON.getJSONArray("array");
            for (int i = 0; i < TimelineMessage.length(); ++i){
                JSONObject messageJSON = TimelineMessage.getJSONObject(i);
                ContentValues messageData = new ContentValues();

                messageData.put("id", messageJSON.getString("id"));
                messageData.put("creator", messageJSON.getJSONObject("creator").getString("fullname"));
                messageData.put("timelineId", messageJSON.getString("timelineId"));
                messageData.put("parentId", messageJSON.getString("parentId"));
                messageData.put("title", messageJSON.getString("title"));
                messageData.put("message", messageJSON.getString("message"));
                messageData.put("parentId", messageJSON.getString("parentId"));
                if (messageJSON.getString("editedAt").equals("null"))
                    messageData.put("Date", messageJSON.getJSONObject("createdAt").getString("date"));
                else
                    messageData.put("Date", messageJSON.getJSONObject("editedAt").getString("date"));
                if (messageJSON.getString("deletedAt").equals("null"))
                    listMessage.add(messageData);

            }
            _context.fillView(listMessage);

        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;

        try {
            String token = SessionAdapter.getInstance().getToken();
            APIConnectAdapter.getInstance().startConnection("timeline/getcomments/" + token + "/" + String.valueOf(_idTimeline) + "/" + String.valueOf(_idMessage), "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            _APIRespond = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", _APIRespond.toString());
            if (_APIRespond == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("Response Comment :", resultAPI);
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