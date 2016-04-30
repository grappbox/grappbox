package com.grappbox.grappbox.grappbox.Whiteboard;

import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 27/03/2016.
 */
public class APIRequestDeleteWhiteboard  extends AsyncTask<String, Void, String> {

    private final static String _PATH = "whiteboard/delete/";
    private Integer _APIResponse;
    private WhiteboardListFragment _context;

    APIRequestDeleteWhiteboard(WhiteboardListFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (_APIResponse >= 200 && _APIResponse < 300)
        {
            APIRequestGetWhiteboardList api = new APIRequestGetWhiteboardList(_context);
            api.execute();
            return;
        }
        Toast.makeText(_context.getContext(), "An error occured", Toast.LENGTH_SHORT).show();
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + SessionAdapter.getInstance().getToken() + "/" + param[0]);
            APIConnectAdapter.getInstance().setRequestConnection("DELETE");

            _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(_APIResponse));
            if (_APIResponse >= 200 && _APIResponse < 300)
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            Log.v("JSON Content", resultAPI);
        } catch (IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
