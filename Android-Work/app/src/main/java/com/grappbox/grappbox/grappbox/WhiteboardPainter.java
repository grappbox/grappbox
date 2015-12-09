package com.grappbox.grappbox.grappbox;

import android.content.Context;
import android.graphics.Paint;

/**
 * Created by Arkanice on 08/12/2015.
 */
public class WhiteboardPainter {

    private static WhiteboardPainter _instance = null;
    private Paint _PainterShape;
    private Paint _PainterText;
    private Context _context;

    private int _PrimeColor = 0xFF333333;
    private int _SecondColor = 0xFF333333;

    private WhiteboardPainter(Context context)
    {
        _context = context;
        _PainterShape = new Paint();
        _PainterText = new Paint();

        _PainterShape.setColor(_PrimeColor);
        _PainterShape.setAntiAlias(true);
        _PainterShape.setStrokeWidth(10);
        _PainterShape.setStyle(Paint.Style.FILL);
        _PainterShape.setStrokeJoin(Paint.Join.BEVEL);
        _PainterShape.setStrokeCap(Paint.Cap.ROUND);

        _PainterText.setColor(_PrimeColor);
        _PainterText.setTextSize(_context.getResources().getDimension(R.dimen.text_little));
    }

    public static WhiteboardPainter getInstance(Context context)
    {
        if (_instance == null)
            _instance = new WhiteboardPainter(context);
        return _instance;
    }

    public Paint Draw()
    {
        return _PainterShape;
    }

    public Paint DrawText()
    {
        return _PainterText;
    }
}
