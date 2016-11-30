package com.grappbox.grappbox.views;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.renderscript.Double2;
import android.view.View;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Marc Wieser the 30/11/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

/**
 * The Whiteboard give you an area where you can draw with
 * specific tools.
 * @see Tool
 */
public class Whiteboard extends View {

    public interface Callbacks{
        void onNewObject(JSONObject object);
        void onDeleteArea(JSONObject object);
    }

    /**
     * Describe the tools available to draw inside this whiteboard.
     * Possible values
     * <li>{@link #E_MOVE}</li>
     * <li>{@link #E_LINE}</li>
     * <li>{@link #E_HANDWRITE}</li>
     * <li>{@link #E_ELLIPSIS}</li>
     * <li>{@link #E_RECTANGLE}</li>
     * <li>{@link #E_DIAMOND}</li>
     * <li>{@link #E_TEXT}</li>
     */
    public enum Tool{
        E_MOVE,
        E_LINE,
        E_HANDWRITE,
        E_ELLIPSIS,
        E_RECTANGLE,
        E_DIAMOND,
        E_TEXT
    }

    /**
     * Describe a basic and incomplete object.
     * @see ShapeModel
     * @see LineModel
     * @see TextModel
     * @see HandwriteModel
     * @see EllipsisModel
     * @see RectangleModel
     * @see DiamondModel
     */
    private abstract class ObjectModel{
        public Tool drawingTool;
        public int strokeColor;
        private Context mContext;

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         * @param type The tool the object have to use to draw itself
         */
        public ObjectModel(Context context, Tool type) {
            super();
            mContext = context;
            drawingTool = type;
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        public void init(JSONObject object) throws JSONException{
            strokeColor = Color.parseColor(object.getString("color"));
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        public abstract void draw(Canvas canvas);
    }

    /**
     * Describe a basic and incomplete shape.
     * Complete it by extending it, but don't use it by itself,
     * please use followings objects instead :
     * @see LineModel
     * @see TextModel
     * @see EllipsisModel
     * @see RectangleModel
     * @see DiamondModel
     * @see ObjectModel
     */
    private abstract class ShapeModel extends ObjectModel{
        public int backgroundColor;
        public Double2 positionStart;
        public Double2 positionEnd;
        public int lineWeight;

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         * @param type The tool the object have to use to draw itself
         */
        public ShapeModel(Context context, Tool type) {
            super(context, type);
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONObject startObject = object.getJSONObject("positionStart");
            JSONObject endObject = object.getJSONObject("positionEnd");

            super.init(object);
            backgroundColor = Color.parseColor(object.getString("background"));
            positionStart = new Double2(startObject.getDouble("x"), startObject.getDouble("y"));
            positionEnd = new Double2(endObject.getDouble("x"), endObject.getDouble("y"));
            lineWeight = object.getInt("lineWeight");
        }
    }

    /**
     * Describe a complete text object into the whiteboard
     * @see ObjectModel
     */
    private class TextModel extends ObjectModel{
        public Double2 positionStart;
        public Double2 positionEnd;
        public String text;
        public int size;
        public boolean isItalic;
        public boolean isBold;

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         */
        public TextModel(Context context) {
            super(context, Tool.E_TEXT);
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONObject startObject = object.getJSONObject("positionStart");
            JSONObject endObject = object.getJSONObject("positionEnd");

            super.init(object);
            positionStart = new Double2(startObject.getDouble("x"), startObject.getDouble("y"));
            positionEnd = new Double2(endObject.getDouble("x"), endObject.getDouble("y"));
            text = object.getString("text");
            size = object.getInt("size");
            isItalic = object.getBoolean("isItalic");
            isBold = object.getBoolean("isBold");
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {

        }
    }

    /**
     * Describe a complete path from handwriting object into the whiteboard
     * @see ObjectModel
     */
    private class HandwriteModel extends ObjectModel{
        public int lineWeight;
        public List<Double2> points;

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         */
        public HandwriteModel(Context context) {
            super(context, Tool.E_HANDWRITE);
            points = new ArrayList<>();
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONArray pointsObject = object.getJSONArray("points");

            super.init(object);
            lineWeight = object.getInt("lineWeight");
            points.clear();
            for (int i = 0; i < pointsObject.length(); ++i){
                JSONObject pointObject = pointsObject.getJSONObject(i);
                points.add(new Double2(pointObject.getDouble("x"), pointObject.getDouble("y")));
            }
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {

        }
    }

    /**
     * Describe a complete ellipsis object into the whiteboard
     * @see ObjectModel
     */
    private class EllipsisModel extends ShapeModel{
        public Double2 radius;

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         */
        public EllipsisModel(Context context) {
            super(context, Tool.E_ELLIPSIS);
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONObject radiusObject = object.getJSONObject("radius");

            super.init(object);
            radius = new Double2(radiusObject.getDouble("x"), radiusObject.getDouble("y"));
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {

        }
    }

    /**
     * Describe a complete line object into the whiteboard
     * @see ObjectModel
     */
    private class LineModel extends ShapeModel{

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         */
        public LineModel(Context context) {
            super(context, Tool.E_LINE);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {

        }
    }

    /**
     * Describe a complete rectangle object into the whiteboard
     * @see ObjectModel
     */
    private class RectangleModel extends ShapeModel{

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         */
        public RectangleModel(Context context) {
            super(context, Tool.E_RECTANGLE);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {

        }
    }

    /**
     * Describe a complete diamond object into the whiteboard
     * @see ObjectModel
     */
    private class DiamondModel extends ShapeModel{

        /**
         * Constructor
         * @param context The context object, you can get it from parent class
         */
        public DiamondModel(Context context) {
            super(context, Tool.E_DIAMOND);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {

        }
    }

    private Tool mCurrentTool;
    private List<ObjectModel> mObjects;
    private List<Callbacks> mCallbacks;

    /**
     * Constructor
     * @param context The context object, it's generally the activity that contain this View
     */
    public Whiteboard(Context context) {
        super(context);
        mCurrentTool = Tool.E_MOVE;
        mObjects = new ArrayList<>();
        mCallbacks = new ArrayList<>();
    }

    /**
     * Change the current tool to user
     * @param type The tool's type to set in the whiteboard
     */
    public void setTool(Tool type){
        mCurrentTool = type;
    }

    /**
     * Register a new listener on whiteboard events if it doesn't exist yet
     * @param listener The implementation of all whiteboard events' reactions
     * @see Whiteboard#unregisterListener(Callbacks)
     */
    public void registerListener(Callbacks listener){
        if (mCallbacks.contains(listener))
            return;
        mCallbacks.add(listener);
    }

    /**
     * Delete a listener on whiteboard events that was registered before
     * @param listener The implementation of all whiteboard events' reactions
     * @see Whiteboard#registerListener(Callbacks)
     */
    public void unregisterListener(Callbacks listener){
        mCallbacks.remove(listener);
    }

    @Override
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);

        for (ObjectModel object : mObjects) {
            object.draw(canvas);
        }
    }
}
