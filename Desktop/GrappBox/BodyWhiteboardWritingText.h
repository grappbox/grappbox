#ifndef BODYWHITEBOARDWRITINGTEXT_H
#define BODYWHITEBOARDWRITINGTEXT_H

#include <QtWidgets/QWidget>
#include <QtWidgets/QTextEdit>
#include <QtWidgets/QCheckBox>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QSpinBox>

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
