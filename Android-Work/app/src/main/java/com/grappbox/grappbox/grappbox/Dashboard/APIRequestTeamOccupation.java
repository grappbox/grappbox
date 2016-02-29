package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;

import java.io.IOException;
import java.util.List;

class APIRequestTeamOccupation extends AsyncTask<String, Void, List<ContentValues>> {

    TeamOccupationFragment _context;

    APIRequestTeamOccupation(TeamOccupationFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(List<ContentValues> result) {
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
            APIConnectAdapter.getInstance().startConnection("dashboard/getteamoccupation/" + SessionAdapter.getInstance().getToken());
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            resultAPI = APIConnectAdapter.getInstance().getInputSream();
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Team Response", String.valueOf(APIResponse));
            if (APIResponse == 200) {
                Log.v("Team Content", resultAPI);
                listResult = APIConnectAdapter.getInstance().getListTeamOccupation(resultAPI);
            }

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return listResult;
    }
}
