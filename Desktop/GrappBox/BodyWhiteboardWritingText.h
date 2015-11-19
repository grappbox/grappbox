#ifndef BODYWHITEBOARDWRITINGTEXT_H
#define BODYWHITEBOARDWRITINGTEXT_H

#include <QWidget>
#include <QTextEdit>
#include <QCheckBox>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QSpinBox>

class BodyWhiteboardWritingText : public QWidget
{
    Q_OBJECT
public:
    BodyWhiteboardWritingText(QWidget *parent);

signals:
    void Accept(QString, bool, bool, int);
    void Cancel();

public slots:
    void OnCancelPush();
    void OnAcceptPush();

private:
    QTextEdit *_Text;
    QCheckBox *_Italic;
    QCheckBox *_Bold;
    QSpinBox *_Size;
    QPushButton *_Cancel;
    QPushButton *_Accept;
    QHBoxLayout *_OptionLayout;
    QHBoxLayout *_ButtonLayout;
    QVBoxLayout *_MainLayout;
};

#endif // BODYWHITEBOARDWRITINGTEXT_H
