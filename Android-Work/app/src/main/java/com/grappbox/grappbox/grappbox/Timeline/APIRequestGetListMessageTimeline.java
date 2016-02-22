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
 * Created by tan_f on 17/02/2016.
 */
public class APIRequestGetListMessageTimeline extends AsyncTask<String, Void, String> {

    private TimelineListFragment _context;
    private int _idTimeline;
    private Integer _APIRespond;

    APIRequestGetListMessageTimeline(TimelineListFragment context, int idTimeline)
    {
        _context = context;
        _idTimeline = idTimeline;
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
            APIConnectAdapter.getInstance().startConnection("timeline/getmessages/" + token + "/" + String.valueOf(_idTimeline), "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            _APIRespond = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", _APIRespond.toString());
            if (_APIRespond == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("Response API :", resultAPI);
            } else {
                return null;
            }

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
