package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;

import java.io.IOException;
import java.util.List;

public class APIRequestNextMeeting extends AsyncTask<String, Void, List<ContentValues>> {

    private final static String _PATH = "dashboard/getnextmeetings/";
    NextMeetingFragment _context;

    APIRequestNextMeeting(NextMeetingFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(List<ContentValues> result)
    {
        super.onPostExecute(result);
        if (result != null)
            _context.createContentView(result);
    }

    @Override
    protected List<ContentValues> doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;
        List<ContentValues> listResult = null;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
            APIConnectAdapter.getInstance().setRequestConnection("GET");
            resultAPI = APIConnectAdapter.getInstance().getInputSream();
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response Meeting", String.valueOf(APIResponse));
            if (APIResponse == 200) {
                Log.v("JSON Meeting", resultAPI);
                listResult = APIConnectAdapter.getInstance().getListNextMeeting(resultAPI);
            }

        } catch (IOException | JSONException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return listResult;
    }

}
