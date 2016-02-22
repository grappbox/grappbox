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
public class APIRequestTimelineArchiveMessage extends AsyncTask<String, Void, String> {
    private TimelineListFragment _context;
    private int _idMessage;
    private int _idTimeline;
    private Integer _APIRespond;

    APIRequestTimelineArchiveMessage(TimelineListFragment context, int idTimeline, int idMessage)
    {
        _context = context;
        _idMessage = idMessage;
        _idTimeline = idTimeline;
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
        Toast.makeText(_context.getContext(), "Archive message complete", Toast.LENGTH_SHORT).show();
        APIRequestGetListMessageTimeline apiGet = new APIRequestGetListMessageTimeline(_context, _idTimeline);
        apiGet.execute();
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;

        try {
            String token = SessionAdapter.getInstance().getToken();
            APIConnectAdapter.getInstance().startConnection("timeline/archivemessage/" + token + "/" + _idTimeline + "/" + String.valueOf(_idMessage), "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("DELETE");

            _APIRespond = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", _APIRespond.toString());
            if (_APIRespond == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
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
